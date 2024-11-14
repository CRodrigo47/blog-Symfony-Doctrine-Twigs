<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Restaurante;
use App\Repository\RestauranteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;


class RestauranteController extends AbstractController  
{
    #[ Route("/restaurante/form", name: 'crear_restaurante', methods: ["GET", "POST"])]
    public function crearRestaurante (EntityManagerInterface $emi, Request $request): Response{
	$restaurante = new Restaurante();
    
    $fb = $this->createFormBuilder($restaurante);
    
    $fb->add("Nombre", TextType::class, [

        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'Bar Ejemplo'
        ]
    ]);
    $fb->add("Direccion", TextType::class, [

        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'C/ Ejemplo 5'
        ]
    ]);
    $fb->add("Telefono", TextType::class, [
        "required" => false,
        "constraints"=>[
            new Length(["min"=>9,"max"=> 12])
        ],
        'attr' => [
            'placeholder' => '999999999'
        ]
    ]);
    $fb->add("Tipo_de_cocina", TextType::class, [
        "required" => false,
        "constraints"=>[
            new Length(["min"=>1,"max"=> 255])
        ],
        'attr' => [
            'placeholder' => 'Española'
        ]
    ]);
    $fb->add("Guardar", SubmitType::class);

    $formulario = $fb->getForm();

    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()){
		$restaurante = $formulario->getData();

        $emi->persist($restaurante);
        $emi->flush();

	return  $this->redirectToRoute("mostrartodos_restaurante");
    
	}else {

	return $this->render("restaurante/crearRestaurante.html.twig", ["formulario" => $formulario]);

    }
    
	}

    #[ Route("/restaurante", name: 'mostrartodos_restaurante', methods: ["GET"])]
    public function listaRestaurantes (RestauranteRepository $repo): Response{

	$listaRestaurantes = $repo->findAll();

    return $this->render('restaurante/index.html.twig',
     ["controller_name" => "Esto muestra todos los restaurantes",
      "listadoRestaurantes" => $listaRestaurantes
    ]);

	}

    #[ Route("/restaurante/{idRestaurante}", name: 'mostrar_restaurante', methods: ["GET"])]
    public function mostrarRestaurante (RestauranteRepository $repo, int $idRestaurante): Response{

	$restaurante = $repo->find($idRestaurante);


    return $this->render('restaurante/mostrarRestaurante.html.twig', [
        "controller_name" => "Este es tu restaurante",
         "restaurante" => $restaurante
        ]);
	}

    #[ Route("/restaurante/form/{idRestaurante}", name: 'actualizar_restaurante', methods: ["GET", "POST"])]
    public function actualizarRestaurante (RestauranteRepository $repo, EntityManagerInterface $emi, int $idRestaurante, Request $request): Response{
        $restaurante = $repo->find($idRestaurante);

        //Hay que hacer lo mismo que el form de crear, pero en lugar de usar un nuevo restaurante vacio, usamos el que pillamos con el repositorio
        
        $fb = $this->createFormBuilder($restaurante);
        $fb->add('Nombre', TextType::class, [
            'attr' => [
                'placeholder' => 'Bar Ejemplo',
            ],
        ]);
        $fb->add('Direccion', TextType::class, [
            'attr' => [
                'placeholder' => 'C/ Ejemplo 3',
            ],
        ]);
        $fb->add('Telefono', TextType::class, [
            'attr' => [
                'placeholder' => '999999999',
            ],
        ]);
        $fb->add('Tipo_de_cocina', TextType::class, [
            'attr' => [
                'placeholder' => 'Española',
            ],
        ]);
        $fb->add('Guardar', SubmitType::class, [
            'label' => 'Guardar cambios',
        ]);
        
        $formulario = $fb->getForm();

        $formulario->handleRequest($request);


        if ($formulario->isSubmitted() && $formulario->isValid()){
            $restaurante = $fb->getData();
            
            $emi->flush();
    
        return  $this->redirectToRoute("mostrartodos_restaurante");
        
        }else {
    
        return $this->render("restaurante/actualizarRestaurante.html.twig", ["formulario" => $formulario]);
    
        }
        
	}

    #[ Route("/restaurante/{idRestaurante}", name: 'eliminar_restaurante', methods: ["POST"])]
    public function eliminarRestaurante (RestauranteRepository $repo, EntityManagerInterface $emi, int $idRestaurante): Response{
        $restaurante = $repo->find($idRestaurante);
    
            if(empty($restaurante->getVisitas()[0])){
                $emi->remove($restaurante);
                $emi->flush();
                //Con la ayuda de chatGPT: los flash sirven para enviar información temporal a la plantilla a la que se redirige.
                //De esta manera puedo redirigir al usuario a la plantilla index e informarle de si se ha eliminado o no su restaurante.
                $this->addFlash('success', 'Restaurante eliminado con éxito.');
            }else{
                $this->addFlash('error', 'No puedes eliminar un restaurante con visitas activas, eliminalas primero.');
            }
            return $this->redirectToRoute("mostrartodos_restaurante");
            
        
	}

    



}
