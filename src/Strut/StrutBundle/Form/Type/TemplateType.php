<?php

namespace Strut\StrutBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'template.form_settings.title_label',
                'required' => true,
            ])
            ->add('template', CheckboxType::class, [
                'label' => 'template.form_settings.template_label',
                'required' => false,
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'template.form_settings.public_label',
                'required' => false,
            ])
            ->add('groupShares', EntityType::class, [
                'label' => 'template.form_settings.groups_label',
                'required' => false,
                'class' => 'Strut\GroupBundle\Entity\Group',
                'multiple'  => true,
                'expanded'  => true,
                'choices' => $this->getGroups($options),
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

    private function getGroups($options) {
    	$groups = [];
		foreach ($options['attr']['user']->getGroups() as $group) {
			if ($options['attr']['user']->inGroup($group)) {
				$groups[] = $group;
			}
		}
		return $groups;
	}
}
