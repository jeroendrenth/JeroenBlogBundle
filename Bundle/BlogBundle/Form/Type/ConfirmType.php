<?php

namespace Jeroen\Bundle\BlogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfirmType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
	    $resolver->setDefaults(array(
	        'data_class' => 'Jeroen\Bundle\BlogBundle\Entity\Blog',
	    ));
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('delete', 'submit')
        ;
    }

    public function getName()
    {
        return 'confirm';
    }
}