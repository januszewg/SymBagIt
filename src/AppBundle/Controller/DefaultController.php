<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
class DefaultController extends Controller
{
  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request)
  {
      return $this->render('default/bagit.html.twig');
  }

  /**
   * @Route("/bagit/check", name="bagit-check")
   */
  public function checkerAction(Request $request)
  {
    $folderpath = $request->request->get('folderpath');

    if(empty($folderpath)) {
      $folderpath = $request->query->get('folderpath');
    }

    require $this->get('kernel')->getRootDir()."/../vendor/scholarslab/bagit/lib/bagit.php";
    $checkfiles = $this->validatefolder($folderpath);

    if (count($checkfiles['files']) == 0) {
      $bag = new \BagIt($folderpath);
      $validation = $bag->validate();

      if (count($validation) > 0) {
        foreach ($validation as $message) {
          $full_message = '';
          foreach ($message as $k => $value) {
            $full_message .= ' ' . $value;
          }
          $checkfiles[] = $full_message;
        }
      }
    }

    if(count($checkfiles) > 0) {
      $checkfiles = array('files' => $checkfiles);
    }

    return new JsonResponse($checkfiles);

  }

  /**
   * @Route("/bagit/update", name="bagit-update")
   */
  public function updateAction(Request $request)
  {
      $folderpath = $request->request->get('folderpath');

      if(empty($folderpath)) {
        $folderpath = $request->query->get('folderpath');
      }

      require $this->get('kernel')->getRootDir()."/../vendor/scholarslab/bagit/lib/bagit.php";
      //$fileparts = pathinfo($folderpath);

      $bag = new \BagIt($folderpath);
      $bag->update();
      return new JsonResponse(array("flag"=>true));

    }
    function validatefolder($path){
        $missingFiles = [];
        if (!file_exists($path.'/bag-info.txt')) {
            $missingFiles[] = 'Baginfo';
        }
        if (!file_exists($path.'/manifest-sha1.txt') && !file_exists($path.'/manifest-md5.txt')) {
            $missingFiles[] = 'Manifest';
        }
        if (!file_exists($path.'/bagit.txt')) {
            $missingFiles[] = 'Bagit';
        }
        if (!file_exists($path.'/tagmanifest-sha1.txt')) {
            $missingFiles[] = 'Tag Manifest';
        }

        $missing = array("files"=>$missingFiles);
        return $missing;
    }
}
