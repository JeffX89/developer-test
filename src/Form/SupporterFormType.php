<?php

namespace App\Form;

use App\Entity\Supporter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupporterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('birthDate', DateType::class, [
                'label' => 'Geboortedatum',
                'placeholder' => [
                    'year' => 'jaar',
                    'month' => 'maand',
                    'day' => 'dag',
                ],
                'format' => 'dd-MM-yyyy',
                'years' => range(date('Y')-120, date('Y')),
                'required' => true
            ])
            ->add('supporterId', IntegerType::class, [
                'label' => 'Lidnummer',
                'required' => true
            ])
            ->add('save', SubmitType::class, ['label'=>'Volgende']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Supporter::class,
        ]);
    }
}
