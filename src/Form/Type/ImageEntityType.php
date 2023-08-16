<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                    MimeTypeUtil::getImageFileConstraint(),
                ],
            ])
        ;

        if ($options['hide_alt']) {
            $builder->add('alt', HiddenType::class, [
                'required' => false,
                'data' => '',
            ]);
        } else {
            $builder->add('alt', TextType::class, [
                'label' => 'Screen Reader Text',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'image_label' => false,
            'file_label' => false,
            'hide_alt' => false,
        ]);
    }
}
