// src/Controller/UploadController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class UploadController extends AbstractController
{
    /**
     * @Route("/api/upload", name="upload", methods={"POST"})
     */
    public function upload(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $file = $request->files->get('file');
        if (!$file) {
            return new Response('No file uploaded', Response::HTTP_BAD_REQUEST);
        }

        $handle = fopen($file->getPathname(), 'r');
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if ($data[0] != 'name') { // Skip the header row
                $user = new User();
                $user->setName($data[0]);
                $user->setEmail($data[1]);
                $user->setUsername($data[2]);
                $user->setAddress($data[3]);
                $user->setRole($data[4]);

                $em->persist($user);

                // Send email
                $email = (new Email())
                    ->from('no-reply@example.com')
                    ->to($data[1])
                    ->subject('Welcome to Our Service')
                    ->text('Your data has been successfully uploaded!');

                $mailer->send($email);
            }
        }

        $em->flush();

        return new Response('File processed successfully', Response::HTTP_OK);
    }
}
