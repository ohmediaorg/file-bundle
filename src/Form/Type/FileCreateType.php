<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $file = $options['data'];

        $mimes = [
            MimeTypeUtil::AUDIO,
            MimeTypeUtil::DOCUMENT,
            MimeTypeUtil::TEXT,
            MimeTypeUtil::VIDEO,
        ];

        if ($file && $file->isImage()) {
            $mimes = [MimeTypeUtil::IMAGE];
        }

        $fileConstraint = MimeTypeUtil::getFileConstraint(...$mimes);
        $mimeTypes = MimeTypeUtil::getMimeTypes(...$mimes);

        $builder
            ->add('file', FileType::class, [
                'label' => $file->isImage() ? 'Image' : 'File',
                'constraints' => [$fileConstraint],
                'attr' => [
                    'accept' => implode(',', $mimeTypes),
                ],
            ])
            ->add('locked', CheckboxType::class, [
                'required' => false,
                'label' => 'Require login to view this file',
            ])
        ;

        if ($file->isImage()) {
            $builder->add('alt', TextType::class, [
                'label' => 'Screen Reader Text',
                'required' => false,
            ]);
        } else {
            $builder->add('alt', HiddenType::class, [
                'required' => false,
                'data' => '',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
