<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Util\FileUtil;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $file = $options['data'];

        $isImage = $file && $file->isImage();

        $mimes = [
            MimeTypeUtil::AUDIO,
            MimeTypeUtil::DOCUMENT,
            MimeTypeUtil::TEXT,
            MimeTypeUtil::VIDEO,
        ];

        if ($isImage) {
            $mimes = [MimeTypeUtil::IMAGE];
        }

        $fileConstraint = MimeTypeUtil::getFileConstraint(...$mimes);
        $mimeTypes = MimeTypeUtil::getMimeTypes(...$mimes);

        $fileConstraint->maxSize = $options['max_size_bytes'];

        $maxSize = FileUtil::formatBytesBinary($options['max_size_bytes'], 2);

        $fileConstraint->maxSizeMessage = "The file is too large. Allowed maximum size is $maxSize.";
        $fileConstraint->uploadIniSizeErrorMessage = $fileConstraint->maxSizeMessage;

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

        if ($isImage) {
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
            'max_size_bytes' => null,
        ]);
    }
}
