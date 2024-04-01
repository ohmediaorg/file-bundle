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

class FileEntityType extends AbstractType
{
    public const ACTION_KEEP = 'keep';
    public const ACTION_REPLACE = 'replace';
    public const ACTION_DELETE = 'delete';

    public const DATA_ATTRIBUTE = 'data-ohmedia-file-widget';

    private $fileManager;
    private $fileRepository;

    public function __construct(
        FileManager $fileManager,
        FileRepository $fileRepository
    ) {
        $this->fileManager = $fileManager;
        $this->fileRepository = $fileRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $file = isset($options['data']) ? $options['data'] : null;

        $fileExists = $file && $file->getPath();

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
                'required' => $fileExists ? false : $options['required'],
                'constraints' => $options['file_constraints'],
                'attr' => [
                    'accept' => implode(',', $accept),
                ],
            ])
        ;

        if ($fileExists) {
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

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            [$this, 'onPostSubmit']
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $file = isset($options['data']) ? $options['data'] : null;

        $view->vars['current_file'] = $file && $file->getPath() ? $file : null;

        $view->vars['DATA_ATTRIBUTE'] = self::DATA_ATTRIBUTE;
    }

    public function onPostSubmit(FormEvent $event)
    {
        $file = $event->getData();
        $form = $event->getForm();

        if (!$form->has('action')) {
            return;
        }

        $action = $form->get('action')->getData();

        if (self::ACTION_DELETE === $action) {
            $file->setFile(null);
        }

        if (in_array($action, [self::ACTION_REPLACE, self::ACTION_DELETE])) {
            $resizes = $file->getResizes();

            foreach ($resizes as $resize) {
                $this->fileRepository->remove($resize);
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
}
