<?php

namespace OHMedia\FileBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use OHMedia\FileBundle\Entity\FileFolder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileFolderMoveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $folder = $options['data'];

        $builder
            ->add('folder', EntityType::class, [
                'class' => FileFolder::class,
                'label' => 'Destination Folder',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($folder) {
                    $subfolders = $folder->getSubfolders();

                    $ids = array_map(function($folder) {
                        return $folder->getId();
                    }, $subfolders);

                    $ids[] = $folder->getId();

                    return $er->createQueryBuilder('ff')
                        ->where('ff.browser = 1')
                        ->andWhere('ff.id NOT IN (:ids)')
                        ->setParameter('ids', $ids)
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
            'data_class' => FileFolder::class,
        ]);
    }
}
