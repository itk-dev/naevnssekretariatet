<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/user", name="admin_user_")
 */
class UserController extends AbstractController
{
    public function __construct(private array $options)
    {
        $this->options = $this->resolveOptions($options);
    }

    /**
     * @Route("/signature/{id}", name="signature_file")
     */
    public function templateFile(User $user): Response
    {
        $filename = $user->getSignatureFilename();
        if (null === $filename) {
            throw new NotFoundHttpException();
        }

        $file = rtrim($this->options['signature_file_directory'] ?? '', '/').'/'.$filename;

        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    private function resolveOptions(array $options)
    {
        return (new OptionsResolver())
            ->setRequired([
                'signature_file_directory',
            ])
            ->resolve($options)
        ;
    }
}
