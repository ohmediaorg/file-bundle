<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\FileFolder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileFolderCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('locked', CheckboxType::class, [
                'required' => false,
                'label' => 'Require login to view this folder and all files within',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FileFolder::class,
        ]);
    }
}
