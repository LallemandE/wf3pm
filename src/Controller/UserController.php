<?php
namespace App\Controller;



use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use App\Repository\RoleRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController
{
    public function register(
        Environment $twig,
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator,
        \Swift_Mailer $mailer,
        EncoderFactoryInterface $encoderFactory,
        RoleRepository $roleRepository
        )
    {
        $user = new User();
        $builder = $factory->createBuilder(FormType::class, $user);
        $builder->add('username', TextType::class, ['label' => 'FORM.USER.NAME', 'required' => true])
        ->add('firstname', TextType::class, ['label' => 'FORM.USER.FIRSTNAME', 'required' => true])
        ->add('lastname', TextType::class, ['label' => 'FORM.USER.LASTNAME','required' => true])
        ->add('email', EmailType::class, ['label' => 'FORM.USER.EMAIL','required' => true])
                ->add('password', RepeatedType::class,array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                    'first_options'  => array('label' => 'FORM.USER.PASSWORD.FIRST'),
                    'second_options' => array('label' => 'FORM.USER.PASSWORD.SECOND'),
                ))
                ->add ('FORM.SUBMIT', SubmitType::class, ['label' => 'FORM.SUBMIT'] );

        
        
        $form = $builder->getForm();
        
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $salt = md5($user->getUsername());
            $user->setSalt($salt);
            
            $encoder = $encoderFactory->getEncoder(User::class);
            $password = $encoder->encodePassword($user->getPassword(), $salt);
            
            $user->setPassword($password);
            
            // ajout du rôle standard pour tous
            
            $user->addRole($roleRepository->findOneByLabel('ROLE_USER'));
            
            $manager->persist($user);
            $manager->flush();
            
            // I send the mail
            // je crée le message
            
            $message = new \Swift_Message();
            $message->setFrom('wf3pm@localhost.com')
                ->setTo($user->getEmail())
                ->setSubject('Validate your registration')
                ->setContentType('text/html')
                ->setBody($twig->render('mail/account_creation.html.twig',
                                ['user' => $user]))
                ->addPart($twig->render('mail/account_creation.txt.twig',
                                    ['user' => $user]), 'text/plain');

                
                
            
            $mailer->send($message);
            
            
            $session->getFlashBag()->add ("info", "User Created");
            
            return new RedirectResponse($urlGenerator->generate('homepage'));
            
        }
        
        return new Response($twig->render('User/register.html.twig', 
            ['formular' => $form->createView()]));
    }
    
    public function  activateUser($token,
        ObjectManager $manager,
        SessionInterface $session,
        UrlGeneratorInterface $urlGenerator,
        RoleRepository $roleRepository)
    {
        $userRepository = $manager->getRepository(User::class);
        $user = $userRepository->findOneByEmailToken($token);
        
        
        
        if (! $user){
            throw new NotFoundHttpException('User Activation / User not found !');
        }
        
        $user->setActive(true)->setEmailToken(null);
        
        // ajout du rôle si la validation se fait
        $user->addRole($roleRepository->findOneByLabel('ROLE_ACTIVE'));
        
        $manager->flush();
        
        
        $session->getFlashBag()->add ("info", "User validated !");
        return new RedirectResponse($urlGenerator->generate('homepage'));
        

    }
    
    public function usernameExist(
                        Request $request,
                        UserRepository $repository)
    {
        $username = $request->request->get('username');
        
        $unavailable = false;
        if (!empty($username)){
            $unavailable = $repository->usernameExist($username);
        }
        return new JsonResponse(['available' => ! $unavailable]);
    }
    
    public function login(AuthenticationUtils $authUtils, Environment $twig)
    {
        return new Response ($twig->render('Security/login.html.twig',
                                            ['last_username' => $authUtils->getLastUsername(),
                                                'error' => $authUtils->getLastAuthenticationError()
                                            ]));
    }
    
}

