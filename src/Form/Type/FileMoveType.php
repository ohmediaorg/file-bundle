<?php

namespace OHMedia\FileBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileMoveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('folder', EntityType::class, [
                'class' => FileFolder::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ff')
                        ->where('ff.browser = 1')
                        ->orderBy('LOWER(ff.name)', 'ASC');
                },
                'placeholder' => '/',
                'choice_label' => function (FileFolder $folder) {
                    return '/'.$folder->getPath();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
