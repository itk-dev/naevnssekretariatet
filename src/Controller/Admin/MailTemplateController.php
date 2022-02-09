<?php

namespace App\Controller\Admin;

use App\Entity\MailTemplate;
use App\Exception\MailTemplateException;
use App\Service\MailTemplateHelper;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/mail-template", name="admin_mail_template_")
 */
class MailTemplateController extends AbstractController
{
    /**
     * @Route("/preview/{id}", name="preview")
     */
    public function preview(Request $request, MailTemplate $mailTemplate, MailTemplateHelper $mailTemplateHelper, AdminUrlGenerator $adminUrlGenerator): Response
    {
        try {
            $entity = filter_var($request->get('with_data'), FILTER_VALIDATE_BOOLEAN) ? $mailTemplateHelper->getPreviewEntity($mailTemplate) : null;
            $fileName = $mailTemplateHelper->renderMailTemplate($mailTemplate, $entity);
            $mimeType = (new MimeTypes())->guessMimeType($fileName);
        } catch (MailTemplateException $exception) {
            $this->addFlash('danger', $exception->getMessage());

            // Redirect to referer or crud controller on error.
            return $this->redirect(
                $request->headers->get('referer') ?? $adminUrlGenerator
                    ->setController(MailTemplateCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($mailTemplate->getId())
                    ->generateUrl()
            );
        }

        return new BinaryFileResponse($fileName, 200, ['content-type' => $mimeType]);
    }

    /**
     * @Route("/data/{id}", name="data")
     */
    public function data(MailTemplate $mailTemplate, MailTemplateHelper $mailTemplateHelper): Response
    {
        // @todo error handling.
        $entity = $mailTemplateHelper->getPreviewEntity($mailTemplate);
        $data = $mailTemplateHelper->getTemplateData($mailTemplate, $entity);

        return new JsonResponse($data);
    }

    /**
     * @Route("/template-file/{id}", name="template_file")
     */
    public function templateFile(MailTemplate $mailTemplate, MailTemplateHelper $mailTemplateHelper): Response
    {
        $file = $mailTemplateHelper->getTemplateFile($mailTemplate);

        return $this->file($file);
    }
}
