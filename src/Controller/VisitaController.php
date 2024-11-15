<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Visita;
use App\Repository\RestauranteRepository;
use App\Repository\VisitaRepository;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType as TypeIntegerType;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Length;

class VisitaController extends AbstractController
{
    #[ Route("/visita/form", name: 'crear_visita', methods: ["GET", "POST"])]
    public function crearVisita (EntityManagerInterface $emi,RestauranteRepository $restauranteRepo, Request $request): Response{
	$visita = new Visita();
    
    $fb = $this->createFormBuilder($visita);
    
    $fb->add("Restaurante", TextType::class, [
        "mapped" => false, // No intenta mapear el valor a la propiedad directamente
        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'Bar ejemplo'
        ]
    ]);
    $fb->add("Valoracion", TypeIntegerType::class, [

        "constraints"=>[
            new Range(["min"=>1,"max"=> 10]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => '10'
        ]
    ]);
    $fb->add("Comentario", TextType::class, [

        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'Comentario ejemplo'
        ]
    ]);
    
    $fb->add("Guardar", SubmitType::class);

    $formulario = $fb->getForm();

    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()){
        $restauranteNombre = $formulario->get("Restaurante")->getData();
        $restaurante = $restauranteRepo->findOneBy(["Nombre" => $restauranteNombre]);
        if($restaurante!=null){
            $visita->setRestaurante($restaurante);

            $emi->persist($visita);
            $emi->flush();
            return  $this->redirectToRoute("mostrartodos_visita");
        }else{
            
            
            return $this->render("visita/crearVisita.html.twig", ["formulario" => $formulario]);

        }
    
	}else {
        //Con la ayuda de chatGPT: los flash sirven para enviar información temporal a la plantilla a la que se redirige.
            //De esta manera puedo redirigir al usuario a la plantilla index e informarle de si se ha eliminado o no su restaurante.
        $this->addFlash("error", "Recuerda poner un Restaurante existente");
	return $this->render("visita/crearVisita.html.twig", ["formulario" => $formulario]);

    }
    
	}

    #[ Route("/visita/formRestaurante/{idRestaurante}", name: 'crear_visita_con_restaurante', methods: ["GET", "POST"])]
    public function crearVisitaPorRestaurante (EntityManagerInterface $emi,RestauranteRepository $restauranteRepo,int $idRestaurante, Request $request): Response{
	$visita = new Visita();
    $restaurante = $restauranteRepo->find($idRestaurante);
    
    $fb = $this->createFormBuilder($visita);
    
    $fb->add("Restaurante", TextType::class, [
        "mapped" => false, // No intenta mapear el valor a la propiedad directamente
        "data" => $restaurante->getNombre(),
        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'Bar ejemplo'
        ]
    ]);
    $fb->add("Valoracion", TypeIntegerType::class, [

        "constraints"=>[
            new Range(["min"=>1,"max"=> 10]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => '10'
        ]
    ]);
    $fb->add("Comentario", TextType::class, [

        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'Comentario ejemplo'
        ]
    ]);
    
    $fb->add("Guardar", SubmitType::class);

    $formulario = $fb->getForm();

    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()){
        $restauranteNombre = $formulario->get("Restaurante")->getData();
        $restaurante = $restauranteRepo->findOneBy(["Nombre" => $restauranteNombre]);
        if($restaurante!=null){
            $visita->setRestaurante($restaurante);

            $emi->persist($visita);
            $emi->flush();
            return  $this->redirectToRoute("mostrartodos_restaurante");
        }else{
            
            
            return $this->render("visita/crearVisitaConRestaurante.html.twig", ["formulario" => $formulario]);

        }
    
	}else {
        //Con la ayuda de chatGPT: los flash sirven para enviar información temporal a la plantilla a la que se redirige.
            //De esta manera puedo redirigir al usuario a la plantilla index e informarle de si se ha eliminado o no su restaurante.
        $this->addFlash("error", "Recuerda poner un Restaurante existente");
	return $this->render("visita/crearVisitaConRestaurante.html.twig", ["formulario" => $formulario]);

    }
    
	}

    #[ Route("/visita", name: 'mostrartodos_visita', methods: ["GET"])]
    public function listaVisitas (VisitaRepository $repo): Response{

	$listaVisitas = $repo->findAll();

    return $this->render('visita/index.html.twig',
     ["controller_name" => "Esto muestra todoas las visitas",
      "listadoVisitas" => $listaVisitas
    ]);
}

#[ Route("/visita/form/{idVisita}", name: 'actualizar_visita', methods: ["GET", "POST"])]
public function actualizarVisita (VisitaRepository $repo,RestauranteRepository $restauranteRepo, EntityManagerInterface $emi, int $idVisita, Request $request): Response{
    $visita = $repo->find($idVisita);

    //Hay que hacer lo mismo que el form de crear, pero en lugar de usar un nuevo restaurante vacio, usamos el que pillamos con el repositorio
    
    $fb = $this->createFormBuilder($visita);
    
    $fb->add("Restaurante", TextType::class, [
        "mapped" => false, // No intenta mapear el valor a la propiedad directamente
        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'Bar ejemplo'
        ],
    'data' => $visita->getRestaurante() ? $visita->getRestaurante()->getNombre() : ''
    ]);
    $fb->add("Valoracion", TypeIntegerType::class, [

        "constraints"=>[
            new Range(["min"=>1,"max"=> 10]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => '10'
        ]
    ]);
    $fb->add("Comentario", TextType::class, [

        "constraints"=>[
            new Length(["min"=>1,"max"=> 256]),
            new NotBlank()
        ],
        'attr' => [
            'placeholder' => 'Comentario ejemplo'
        ]
    ]);
    
    $fb->add("Guardar", SubmitType::class);

    $formulario = $fb->getForm();

    $formulario->handleRequest($request);

    if ($formulario->isSubmitted() && $formulario->isValid()){
        $restauranteNombre = $formulario->get("Restaurante")->getData();
        $restaurante = $restauranteRepo->findOneBy(["Nombre" => $restauranteNombre]);
        if($restaurante!=null){
            $visita->setRestaurante($restaurante);

            $emi->flush();
            return  $this->redirectToRoute("mostrartodos_visita");
        }else{
            
            return $this->render("visita/actualizarVisita.html.twig", ["formulario" => $formulario]);

        }
    
	}else {
        
        $this->addFlash("error", "Recuerda poner un Restaurante existente");
	    return $this->render("visita/actualizarVisita.html.twig", ["formulario" => $formulario]);
    }
    
}

#[ Route("/visita/{idVisita}", name: 'eliminar_visita', methods: ["POST"])]
public function eliminarVisita (VisitaRepository $repo, EntityManagerInterface $emi, int $idVisita): Response{
    $visita = $repo->find($idVisita);

            $emi->remove($visita);
            $emi->flush();
            
        return $this->redirectToRoute("mostrartodos_visita");
        
    
}
}
