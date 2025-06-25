<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadWordImageTypeForm extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new File([
                                 'maxSize' => '1024k',
                                 'mimeTypes' => [
                                     'image/jpeg',
                                     'image/png',
                                     'image/gif',
                                 ],
                                 'mimeTypesMessage' => 'Please upload a valid PDF document',
                             ])
                ],
            ])
            ->add('word', HiddenType::class, [
                'mapped' => false
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                                   'csrf_protection' => false,
                               ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
