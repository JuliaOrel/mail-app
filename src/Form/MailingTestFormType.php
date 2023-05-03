<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingTestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('field_user_id', TextType::class, [
                "label" => "User ID",
                "required" => true,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('field_name', TextType::class, [
                "label" => "First Name",
                "required" => true,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('field_last_name', TextType::class, [
                "label" => "Last Name",
                "required" => true,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('field_email', EmailType::class, [
                "label" => "Email",
                "required" => true,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('Send', SubmitType::class, [
                "label" => "SEND",
                "attr" => [
                    "class" => "mt-2 btn btn-success"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
