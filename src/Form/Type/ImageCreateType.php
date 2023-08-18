<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileEntityType::class, [
                'label' => false,
                'file_label' => 'Image',
                'file_constraints' => [
                    MimeTypeUtil::getImageFileConstraint(),
                ],
            ])
            ->add('alt', TextType::class, [
                'label' => 'Screen Reader Text',
                'required' => false,
            ])
            ->add('locked', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Require login to view this image',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
