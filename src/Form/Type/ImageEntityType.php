<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class ImageEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $image = isset($options['data']) ? $options['data'] : null;

        $builder
            ->add('file', FileEntityType::class, [
                'label' => $options['image_label'],
                'file_label' => $options['file_label'],
                'required' => $options['required'],
                'data' => $image ? $image->getFile() : null,
                'file_constraints' => [
                    new FileConstraint([
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/svg', 'image/svg+xml'],
                        'mimeTypesMessage' => 'Only JPG/PNG/GIF/SVG is accepted for upload.'
                    ])
                ],
            ])
            ->add('alt', TextType::class, [
                'label' => 'Screen Reader Text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'image_label' => false,
            'file_label' => false,
        ]);
    }
}
