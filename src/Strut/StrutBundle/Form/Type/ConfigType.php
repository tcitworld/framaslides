<?php

namespace Strut\StrutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    private $languages = [];

    /**
     * @param array $languages Languages come from configuration, array just code language as key and label as value
     */
    public function __construct($languages)
    {
        $this->languages = $languages;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', ChoiceType::class, [
                'choices' => array_flip($this->languages),
                'label' => 'config.form_settings.language_label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'config.form.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Strut\StrutBundle\Entity\Config',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'config';
    }
}
