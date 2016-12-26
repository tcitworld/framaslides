<?php

namespace Strut\StrutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('template', CheckboxType::class, [
                'label' => 'template.form_settings.template_label',
                'required' => false,
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'template.form_settings.public_label',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Strut\StrutBundle\Entity\Presentation',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'template';
    }
}
