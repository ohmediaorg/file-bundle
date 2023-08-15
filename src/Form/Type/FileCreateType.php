<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class FileCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $accept = [];

        foreach ($options['file_constraints'] as $constraint) {
            if (!($constraint instanceof FileConstraint)) {
                continue;
            }

            $mimeTypes = (array) $constraint->mimeTypes;

            $accept = array_merge($accept, $mimeTypes);
        }

        $builder
            ->add('file', FileType::class, [
                'constraints' => $options['file_constraints'],
                'attr' => [
                    'accept' => implode(',', $accept),
                ],
            ])
            ->add('locked', CheckboxType::class, [
                'required' => false,
                'label' => 'Require login to view this file',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => File::class,
            'file_constraints' => [],
        ]);
    }
}
