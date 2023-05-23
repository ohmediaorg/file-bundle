<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Service\FileManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class FileEntityType extends AbstractType
{
    const ACTION_KEEP = 'keep';
    const ACTION_REPLACE = 'replace';
    const ACTION_DELETE = 'delete';

    private $manager;

    public function __construct(FileManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $file = isset($options['data']) ? $options['data'] : null;

        $fileExists = $file && $file->getPath();

        $accept = [];

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
                $this->manager->getWebPath($file),
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
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $file = isset($options['data']) ? $options['data'] : null;

        $view->vars['current_file'] = $file && $file->getPath() ? $file : null;

        $view->vars['action_replace'] = self::ACTION_REPLACE;

        $view->vars['file_required'] = $options['required'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'row_attr' => ['class' => 'file-entity-type'],
            'data_class' => File::class,
            'file_constraints' => [],
            'file_label' => false,
        ]);
    }
}
