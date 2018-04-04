<?php
namespace App\Controller;

use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserController
{
    public function register(
        Environment $twig,
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        SessionInterface $session
        )
    {
        $user = new App\entity\User();
        $builder = $factory->createBuilder(FormType::class, $user);
        $builder->add('pseudo', TextType::class)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('pwd', PasswordType::class)
            ->add('pwd2', PasswordType::class);
        
        $form = $builder->getForm();
        
        $form->handleRequest($request);
        
        
            
        return new Response($twig->render('User/register.html.twig'));
        
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            
            $session->getFlashBag()->add ("info", "User Created");
            
            return new RedirectResponse('/');
            
        }
        
        return new Response($twig->render('User/register.html.twig', 
            ['formular' => $form->createView()]));
    }
    
}

