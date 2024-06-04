<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

use function Symfony\Component\String\u;

class FileEntityType extends AbstractType
{
    public const ACTION_KEEP = 'keep';
    public const ACTION_REPLACE = 'replace';
    public const ACTION_DELETE = 'delete';

    public const DATA_ATTRIBUTE = 'data-ohmedia-file-widget';

    private bool $isMapped = false;

    public function __construct(
        private FileManager $fileManager,
        private FileRepository $fileRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $file = isset($options['data']) ? $options['data'] : null;

        if (null === $options['image']) {
            $options['image'] = $file ? $file->isImage() : false;
        }

        $accept = [];

        if (!$options['file_constraints']) {
            $options['file_constraints'] = $options['image']
                ? [MimeTypeUtil::getImageFileConstraint()]
                : [MimeTypeUtil::getAllFileConstraint()];
        }

        foreach ($options['file_constraints'] as $constraint) {
            if (!($constraint instanceof FileConstraint)) {
                continue;
            }

            $mimeTypes = (array) $constraint->mimeTypes;

            $accept = array_merge($accept, $mimeTypes);
        }

        $builder
            ->add('file', FileType::class, [
                'label' => $options['file_label'],
                'required' => $file ? false : $options['required'],
                'constraints' => $options['file_constraints'],
                'attr' => [
                    'accept' => implode(',', $accept),
                ],
            ])
        ;

        if ($file) {
            $keepLabel = sprintf(
                'Keep the current file <a target="_blank" href="%s">%s</a>',
                $this->fileManager->getWebPath($file),
                $file->getFilename()
            );

            $choices = [
                $keepLabel => self::ACTION_KEEP,
                'Upload a new file' => self::ACTION_REPLACE,
            ];

            if (!$options['required']) {
                $choices['Delete after submit'] = self::ACTION_DELETE;
            }

            $builder->add('action', ChoiceType::class, [
                'mapped' => false,
                'expanded' => true,
                'data' => self::ACTION_KEEP,
                'label' => false,
                'label_html' => true,
                'choices' => $choices,
            ]);
        }

        if ($options['image'] && $options['show_alt']) {
            $builder->add('alt', TextType::class, [
                'label' => 'Screen Reader Text',
                'required' => false,
            ]);
        } else {
            $builder->add('alt', HiddenType::class, [
                'required' => false,
                'data' => '',
            ]);
        }

        $builder->add('image', HiddenType::class, [
            'required' => false,
            'data' => $options['image'],
            'empty_data' => false,
        ]);

        $this->isMapped = $builder->getMapped();

        $builder->addEventListener(
            FormEvents::SUBMIT,
            [$this, 'onSubmit']
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            [$this, 'onPostSubmit']
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $file = isset($options['data']) ? $options['data'] : null;

        $view->vars['current_file'] = $file;

        $view->vars['DATA_ATTRIBUTE'] = self::DATA_ATTRIBUTE;
    }

    private ?File $fileToRemove = null;

    public function onSubmit(FormEvent $event)
    {
        $file = $event->getData();
        $form = $event->getForm();

        $removeFile = $this->shouldRemoveFile($form, $file);

        if ($removeFile) {
            $this->fileToRemove = $file->getId() ? $file : null;

            $parentData = $form->getParent()->getData();

            $name = $form->getName();

            $method = 'set'.u($name)->camel()->title();

            if (is_object($parentData) && method_exists($parentData, $method) && $this->isMapped) {
                call_user_func_array([$parentData, $method], [null]);
            } elseif (is_array($parentData) && isset($parentData[$name])) {
                unset($parentData[$name]);
            }

            $event->setData(null);
            $form->getParent()->remove($form->getName());
        }
    }

    public function onPostSubmit(FormEvent $event)
    {
        $file = $event->getData();
        $form = $event->getForm();

        if ($this->fileToRemove) {
            $this->fileRepository->remove($this->fileToRemove, true);

            return;
        }

        if (!$form->has('action')) {
            return;
        }

        if (self::ACTION_REPLACE === $form->get('action')->getData()) {
            $resizes = $file->getResizes();

            foreach ($resizes as $resize) {
                $this->fileRepository->remove($resize, true);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
            'file_constraints' => [],
            'file_label' => false,
            'image' => null,
            'show_alt' => true,
        ]);
    }

    private function shouldRemoveFile(FormInterface $form, File $file): bool
    {
        if (!$form->getData() && !$file->getFile()) {
            // no previous data and no file selected
            return true;
        }

        if (!$form->has('action')) {
            return false;
        }

        return self::ACTION_DELETE === $form->get('action')->getData();
    }
}
