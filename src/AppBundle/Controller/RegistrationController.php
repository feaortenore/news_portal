<?php
namespace AppBundle\Controller;

use AppBundle\Form\RegistrationForm;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        // 1) build the form
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // 3) Encode the password (you could also do this via Doctrine listener)
            $repository = $em->getRepository('AppBundle:User');
            $existedUser = $repository->findOneEmail($user->getEmail());

            if ($existedUser){
                return $this->render(
                    'user/register.html.twig',
                    array('form' => $form->createView())
                );
            }

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRole(0);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_login');
        }

        return $this->render(
            'user/register.html.twig',
            array('form' => $form->createView())
        );
    }
}