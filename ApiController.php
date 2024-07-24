namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/upload", methods={"POST"})
     */
    public function upload(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $file = $request->files->get('file');
        if (!$file) {
            return new Response('No file uploaded', Response::HTTP_BAD_REQUEST);
        }

        $handle = fopen($file->getPathname(), 'r');
        while (($data = fgetcsv($handle)) !== false) {
            $user = new User();
            $user->setName($data[0]);
            $user->setEmail($data[1]);
            $user->setUsername($data[2]);
            $user->setAddress($data[3]);
            $user->setRole($data[4]);
            $em->persist($user);
            $this->sendEmail($mailer, $data[1]);
        }
        fclose($handle);

        $em->flush();

        return new Response('Data uploaded successfully');
    }

    private function sendEmail(MailerInterface $mailer, $emailAddress)
    {
        $email = (new Email())
            ->from('noreply@example.com')
            ->to($emailAddress)
            ->subject('Data Stored')
            ->text('Your data has been successfully stored.');

        $mailer->send($email);
    }
}
