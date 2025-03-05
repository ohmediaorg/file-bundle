<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileBrowser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileMoveType extends AbstractType
{
    public function __construct(private FileBrowser $fileBrowser)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('folder', EntityType::class, [
                'class' => FileFolder::class,
                'label' => 'Destination Folder',
                'required' => false,
                'choices' => $this->fileBrowser->getFolderChoices(),
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
