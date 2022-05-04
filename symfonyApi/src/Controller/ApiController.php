<?php

namespace App\Controller;

use App\Entity\Region;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    private  $serializer;
    private  $em;

    public function __construct(SerializerInterface $serializer,  EntityManagerInterface $em,)
    {
        $this->serializer = $serializer;
        $this->em = $em;
    }


    // ---------------- REGIONS ---------------------------------------//

    #[Route('/api/regions', name: 'api')]
    public function index(SerializerInterface $serializer, EntityManagerInterface $em): Response
    {

        // Methode  1 JSON > Object  (decode, denormalize)

        //récuperer des Régions en JSON
        $regionJson = file_get_contents("https://geo.api.gouv.fr/regions");

        /*

        //décode Json to Array 
        $regionTab = $serializer->decode($regionJson, "json");

        //denormalize Array to Object 

        $regionObject = $serializer->denormalize($regionTab, "App\Entity\Region[]"); 

        */

        //method 2  JSON > Object (deserialize)

        $regionObject = $serializer->deserialize($regionJson, "App\Entity\Region[]", 'json');

        // dd($regionObject); 


        foreach ($regionObject  as $region) {
            $em->persist($region);
        }

        $em->flush();

        return  new JsonResponse("success", Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/show_regions', name: 'get_regions_api_BD', methods: ['GET'])]
    public function showRegion(SerializerInterface $serializer, RegionRepository $regionRepository)
    {
        // Recupérer tous les régions dans la base de données 
        $regionsObject = $regionRepository->findAll();

        //Serialize Object to JSON
        $regionJson = $serializer->serialize($regionsObject, "json");

        return new JsonResponse($regionJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/regions/new', name: 'post_regions_api_BD')]
    public function createRegion(Request $request, ManagerRegistry $registry, ValidatorInterface $validator)
    {
    
        //récupérer les entrée 
        $regionJson = $request->getContent();


        // deserializer mettre les données json > en object
        $regionObject = $this->serializer->deserialize($regionJson, Region::class, 'json');


        //récuperer les erreurs parrapport au 
        $errors = $validator->validate($regionObject); 

        // dd($errors); 

        if($errors->count() > 0){
            $errorsString = (string) $errors;
            return new Response($errorsString);
        }

        //enregistrer dans la base de donnée
        $entityManager = $registry->getManager();
        $entityManager->persist($regionObject);
        $entityManager->flush();

        //  dd($regionObject);
        return new JsonResponse(" success ", Response::HTTP_OK, [], true);
    }

    // ---------------- DEPARTEMENTS ---------------------------------------//

    #[Route('/api/departements', name: 'departement_api')]
    public function departementApi(){
        
        //récuperer des Département en JSON
        $departementJson = file_get_contents("https://geo.api.gouv.fr/departements");

        //method 2  JSON > Object (deserialize)

        $departementObject = $this->serializer->deserialize($departementJson,
            "App\Entity\Departement[]",
            'json'
        );

        //dd($departementObject);

        foreach ($departementObject as $dept) {
            $this->em->persist($dept);
        }

        $this->em->flush();

        return  new JsonResponse("success", Response::HTTP_CREATED, [], true);

    }

  

    #[Route('/api/show_departements', name: 'get_dept_api_BD', methods: ['GET'])]
    public function showDept(SerializerInterface $serializer, DepartementRepository $deptRepository)
    {
        // Recupérer tous les régions dans la base de données 
        $deptObject = $deptRepository->findAll();

        //Serialize Object to JSON
        $deptJson = $serializer->serialize($deptObject, "json");

        return new JsonResponse($deptJson, Response::HTTP_OK, [], true);
    }


    #[Route('/api/departements/new', name: 'post_dept_api_BD')]
    public function createDept(Request $request, ManagerRegistry $registry, ValidatorInterface $validator)
    {

        //récupérer les entrée 
        $deptJson = $request->getContent();


        // deserializer mettre les données json > en object
        $deptObject = $this->serializer->deserialize($deptJson, Departement::class, 'json');


        //récuperer les erreurs parrapport au 
        $errors = $validator->validate($deptObject);

        // dd($errors); 

        if ($errors->count() > 0) {
            $errorsString = (string) $errors;
            return new Response($errorsString);
        }

        //enregistrer dans la base de donnée
        $entityManager = $registry->getManager();
        $entityManager->persist($deptObject);
        $entityManager->flush();

        //  dd($regionObject);
        return new JsonResponse(" success ", Response::HTTP_OK, [], true);
    }

    // ------------------------- COMMUNES --------------------------------- //


}
