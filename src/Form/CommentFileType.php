<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Dto\FileDto;

/**
 *
 * @author Etudiant
 *        
 */
class CommentFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO Auto-generated method stub
        $builder->add('file', FileType::class);
        
        // si le formulaire est indépendant, je dois afficher le bouton submit
        if ($options['stateless']){
            $builder->add('submit', SubmitType::class);
        }
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // TODO Auto-generated method stub
        // le resolver permet de gérer les options du form
        // on lui applique deux valeurs par défaut.
        $resolver->setDefault('data_type', FileDto::class);
        $resolver->setDefault('stateless', false);
    }

}

