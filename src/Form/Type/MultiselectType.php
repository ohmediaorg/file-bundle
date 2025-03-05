<?php

namespace OHMedia\FileBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultiselectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $folder = $options['folder'];

        $builder->add('files', EntityType::class, [
            'class' => File::class,
            'multiple' => true,
            'expanded' => true,
            'query_builder' => function (EntityRepository $er) use ($folder) {
                $qb = $er->createQueryBuilder('f')
                    ->where('f.browser = 1')
                    ->orderBy('LOWER(f.name)', 'ASC');

                if ($folder) {
                    $qb->andWhere('f.folder = :folder')
                        ->setParameter('folder', $folder);
                }

                return $qb;
            },
        ]);

        $builder->add('folder', EntityType::class, [
            'class' => FileFolder::class,
            'label' => 'Destination Folder',
            'required' => false,
            'query_builder' => function (EntityRepository $er) use ($folder) {
                $qb = $er->createQueryBuilder('ff')
                    ->where('ff.browser = 1')
                    ->orderBy('LOWER(ff.name)', 'ASC');

                if ($folder) {
                    $qb->andWhere('ff.id <> :id')
                        ->setParameter('id', $folder->getId());
                }

                return $qb;
            },
            'placeholder' => '/',
            'choice_label' => function (FileFolder $folder) {
                return '/'.$folder->getPath();
            },
        ]);

        $builder->add('move', SubmitType::class);

        $builder->add('delete', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'folder' => null,
        ]);
    }
}
