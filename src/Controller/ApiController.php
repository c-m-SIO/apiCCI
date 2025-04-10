<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Produit;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): JsonResponse
    {
        // Par exemple, vous pouvez renvoyer une liste d'API disponibles
        return new JsonResponse([
            'message' => 'Bienvenue sur l\'API',
            'routes' => [
                'GET /api/produit/{id}' => 'Obtenez un produit par son ID'
            ]
        ]);
    }

    #[Route('/api/produit/{id}', name: 'app_api_produit_id', methods: ['GET'])]
    public function getProduitById(ProduitRepository $produitRepository, SerializerInterface $serializer, int $id): JsonResponse
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            // Produit non trouvé, retourner une erreur 404
            return new JsonResponse([
                'error' => 'Produit non trouvé'
            ]);
        }

        // Sérialiser l'objet produit en JSON
        $jsonContent = $serializer->serialize($produit, 'json');

        // Retourner une réponse JSON
        return new JsonResponse($jsonContent, 200, [], true);  // Le dernier paramètre "true" signifie qu'on envoie déjà du JSON.
    }

    #[Route('/api/produit/add', name: 'app_api_produit_add', methods: ['POST'])]
    public function addProduit(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator):JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        // Désérialiser les données JSON en un objet Produit
        $produit = $serializer->deserialize(json_encode($data), Produit::class, 'json');

        // Ajoute le produi en BDD
        $entityManager->persist($produit);
        $entityManager->flush();

        // Valider l'objet produit
        $errors = $validator->validate($produit);

        if (count($errors) > 0) {
            // Si il y a des erreurs de validation, les renvoyer dans la réponse
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'errors' => $errorMessages
            ]);
        }

        if (!$produit) {
            // Produit non trouvé, retourner une erreur 404
            return new JsonResponse([
                'error' => 'Produit non trouvé'
            ]);
        }

        // Sérialiser l'objet produit en JSON
        $jsonContent = $serializer->serialize($produit, 'json');
        return new JsonResponse([
            'message' => 'produit ajouté'
        ]);
    }
}
