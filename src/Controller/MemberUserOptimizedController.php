<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/muo")
 */
class MemberUserOptimizedController extends AbstractController
{
    public function index()
    {
        return $this->render('member_user_optimized/index.html.twig', [
            'controller_name' => 'MemberUserOptimizedController',
            ]);
    }
        
    /**
     * @Route("/ajax", name="member_user_optimized")
     */
    public function indexAjax(Request $request): Response //,MemberUserOptimizer $memUsOptim
    {
        // $memUsOptim->ReadRepositoriesAndCompleteCollectionsNarrow($request->query->get("str"));
        // return $this->render('member_user/indexAjax.html.twig', [
        //     'member_users' => $memUsOptim->getUsersList(),
        // ]);
        $content = '';
        $stringToExplode = $request->query->get("str");
        $resultArray = explode(" ",$stringToExplode);
        foreach($resultArray as $line)
        {
            $content .='<br>'.$line; 
        }
        return new Response($content);
    }
}
