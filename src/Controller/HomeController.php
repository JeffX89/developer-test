<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Orders;
use App\Entity\Supporter;
use App\Form\SupporterFormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="home")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function index(Request $request)
    {
        $supporter = new Supporter();
        $form = $this->createForm(SupporterFormType::class, $supporter);

        //clear session when going to index
        $this->get('session')->set('loggedInSupporter', null);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $supporter = $form->getData();

            // check if supporter exists in database.
            $repository = $this->getDoctrine()->getRepository(Supporter::class);
            $existingSupporter = $repository->findOneBy([
                'birthDate' => $supporter->getBirthDate(),
                'supporterId' => $supporter->getSupporterId(),
            ]);
            if (empty($existingSupporter)){
                $form->addError(new FormError('Deze combinatie geboortedatum en lidnummer zijn niet geregistreerd'));
            }

            // check if supporter had already ordered
            $repository = $this->getDoctrine()->getRepository(Orders::class);
            $existingOrder = $repository->findOneBy([
                'supporterId' => $existingSupporter->getId(),
            ]);
            if (!empty($existingOrder)){
                $form->addError(new FormError('U heeft reeds een bestelling geplaatst'));
            }

            // set supporter in session and go to order page
            if ($form->getErrors()->count() === 0) {
                $this->get('session')->set('loggedInSupporter', $existingSupporter->getId());
                return $this->redirectToRoute('order');
            }

        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order", name="order")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function order(Request $request)
    {
        // send users to homepage when session is not set
        $supporterId = $this->get('session')->get('loggedInSupporter');
        if (empty($supporterId)) {
            return $this->redirectToRoute('home');
        }

        echo 'logged in userid: '.$supporterId;

        //get all articles and display in form
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findAll();
        $articleFormChoices = [];
        foreach ($articles as $article) {
            $articleFormChoices[$article->getName()] = $article->getId();
        }
        $order = new Orders();
        $form = $this->createFormBuilder($order)
            ->add('articleId', ChoiceType::class, array(
            'choices'   => $articleFormChoices,
            'required'  => true,
            'multiple'  => false,
            'expanded'  => true,
            ))
            ->add('save', SubmitType::class, ['label'=>'Verzenden'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();
            $order->setSupporterId($supporterId);

            // check if order already exists for supporter .
            $repository = $this->getDoctrine()->getRepository(Orders::class);
            $existingOrder = $repository->createQueryBuilder('o')
                ->andWhere('o.supporterId = :supporter')
                ->setParameter('supporter', $supporterId)
                ->getQuery()
                ->execute();
            if (!empty($existingOrder)){
                $form->addError(new FormError('U heeft reeds een bestelling geplaatst'));
            }

            // save order and go to success page
            if ($form->getErrors()->count() === 0) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($order);
                $entityManager->flush();
                return $this->redirectToRoute('order/success');
            }
        }

        return $this->render('home/order.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order/success", name="order_success")
     */
    public function order_success()
    {
        //logout
        $this->get('session')->set('loggedInSupporter', null);
        return $this->render('home/order_success.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}
