<?php

namespace OHMedia\FileBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use OHMedia\FileBundle\Entity\FileFolder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileFolderEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $folder = $options['data'];

        $builder
            ->add('name')
            ->add('folder', EntityType::class, [
                'class' => FileFolder::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($folder) {
                    return $er->createQueryBuilder('ff')
                        ->where('ff.browser = 1')
                        ->andWhere('ff.id <> :id')
                        ->setParameter('id', $folder->getId())
                        ->orderBy('LOWER(ff.name)', 'ASC');
                },
                'placeholder' => '/',
                'choice_label' => function (FileFolder $folder) {
                    return '/'.$folder->getPath();
                },
            ])
            ->add('locked', CheckboxType::class, [
                'required' => false,
                'label' => 'Require login to view this folder and all files within',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FileFolder::class,
        ]);
    }
}
