<?php

namespace AppBundle\Form;

use AppBundle\Entity\Files;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class FilesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileName')
            ->add('description')
            ->add('date')
            ->add('subject',EntityType::class,array(
                'class'=>"AppBundle\Entity\Subjects",
                'choice_label'=>function ($przedmioty) {
                    return $przedmioty->getName();
                },
                'expanded'=>false,
                'multiple'=>false,
                'by_reference' => false,
            ))
            ->add('brochure', FileType::class,array('data_class' => null , 'label' => 'Brochure (PDF file)'))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Files::class
        ));
    }
}
