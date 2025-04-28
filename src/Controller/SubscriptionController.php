<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;

class SubscriptionController extends AbstractController
{
    #[Route('/tarifs', name: 'app_tarifs')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function tarifs(): Response
    {
        return $this->render('subscription/upgrade.html.twig');
    }

    #[Route('/upgrade', name: 'app_upgrade')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function upgrade(Request $request): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        /** @var User $user */
        $user = $this->getUser();

        $plan = $request->query->get('plan', 'monthly');

        $priceId = $_ENV['STRIPE_PRICE_ID_MONTHLY'];
        if ($plan === 'annually') {
            $priceId = $_ENV['STRIPE_PRICE_ID_ANUALLY'];
        }

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $user->getEmail(),
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/payment/success', name: 'payment_success')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function success(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setIsPremium(true);
        $entityManager->flush();

        $this->addFlash('success', 'Félicitations, vous êtes maintenant Premium 🚀');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_tarifs');
    }
}
