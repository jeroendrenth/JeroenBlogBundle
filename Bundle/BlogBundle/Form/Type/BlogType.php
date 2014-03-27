<?php

namespace Jeroen\Bundle\BlogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ivory\CKEditorBundle\IvoryCKEditorBundle;

class BlogType extends AbstractType
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
         	->add('title', 'text')
            ->add('body', 'ckeditor', array(
                'config' => array(
                    'toolbar' => array(
                        array(
                            'name'  => 'document',
                            'items' => array('Source', '-', 'Save', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates'),
                        ),
                        '/',
                        array(
                            'name'  => 'basicstyles',
                            'items' => array('Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'),
                        ),
                    ),
                    'uiColor' => '#ffffff',
                    //...
                ),
            ))
            ->add('save', 'submit')
        ;
    }

    public function getName()
    {
        return 'blog';
    }
}