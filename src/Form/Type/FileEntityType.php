<?php

namespace OHMedia\FileBundle\Form\Type;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Service\FileManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileEntityType extends AbstractType
{
    private $manager;
    
    public function __construct(FileManager $manager)
    {
        $this->manager = $manager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $file = isset($options['data']) ? $options['data'] : null;
        
        $help = 'The current file is <a target="_blank" href="%s">%s</a>. Upload a new file to replace it.';
        
        $builder
            ->add('file', FileType::class, [
                'label' => $options['file_label'],
                'required' => false,
                'help' => $file && $file->getPath()
                    ? sprintf(
                        $help,
                        $this->manager->getWebPath($file),
                        $file->getName()
                    )
                    : '',
                'help_html' => true,
                'constraints' => $options['file_constraints']
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => File::class,
            'file_constraints' => [],
            'file_label' => false
        ]);
    }
}
