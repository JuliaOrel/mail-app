<?php

namespace App\Form;

use App\Entity\Mailing;
use App\Utils\Manager\MailingManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                "label" => "title",
                "required" => true,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('Body', TextareaType::class, [
                "label" => "Body",
                "required" => true,
                "attr" => [
                    "class" => "form-control text-editor",
                    "rows" => 30,
                ]
            ])
            ->add('scheduledAt')
            ->add('quantity')
            ->add('category', ChoiceType::class, [
                "label" => "Category",
                'choices'  => MailingManager::categoryOptions(),
                "required" => true,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('status', ChoiceType::class, [
                "label" => "Status",
                'choices'  => MailingManager::statusOptions(),
                "required" => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add("mailingImages", FileType::class, [
                "label" => "Choose image",
                'multiple' => true,
                'mapped' => false,
                "required" => false,
                "attr" => [
                    "class" => "form-control mb-4",
                    "multiple" => "multiple"
                ]
            ])
            ->add('isPublished', CheckboxType::class, [
                "label" => "Published",
                "required" => false,
                "attr" => [
                    "class" => "form-check-input mb-4"
                ]
            ])
            ->add('save', SubmitType::class, [
                "label" => "SAVE",
                "attr" => [
                    "class" => "form-control btn btn-success"
                ]
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mailing::class,
        ]);
    }
}
