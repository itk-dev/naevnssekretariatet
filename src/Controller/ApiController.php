<?php

namespace App\Controller;

use App\Exception\ApiException;
use App\Message\NewWebformSubmissionMessage;
use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public function __construct(private MessageBusInterface $bus, private $tvist1ApiToken, private BoardRepository $boardRepository)
    {
    }

    /**
     * @Route("/api/new/case", name="api_add_to_queue", methods={"POST"})
     *
     * @throws ApiException
     */
    public function addNewCaseToQueue(Request $request): Response
    {
        try {
            // Ensure token is correct
            // @see https://github.com/itk-dev/os2forms_selvbetjening/tree/develop/web/modules/custom/os2forms_api_request_handler#request-body
            $apiToken = $request->headers->get('authorization');

            // Replace only first occurrence, in the very unlikely scenario that the token actually contains 'Token '.
            $apiToken = preg_replace('/Token /', '', $apiToken, 1);

            if ($this->tvist1ApiToken !== $apiToken) {
                $message = sprintf('API token %s is invalid.', $apiToken);

                throw new \InvalidArgumentException($message, 401);
            }

            $dataArray = json_decode($request->getContent(), true);

            $this->bus->dispatch(new NewWebformSubmissionMessage($dataArray));

            return new Response('OK');
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/api/complaint-categories/{board_id}", name="api_get_complaint_categories", methods={"GET"})
     */
    public function getComplaintCategoriesForSelect(string $board_id, Request $request): Response
    {
        try {
            // Ensure token is correct
            $apiToken = $request->headers->get('authorization');

            // Replace only first occurrence, in the very unlikely scenario that the token actually contains 'Token '.
            $apiToken = preg_replace('/Token /', '', $apiToken, 1);

            if ($this->tvist1ApiToken !== $apiToken) {
                $message = sprintf('API token %s is invalid.', $apiToken);
                throw new \InvalidArgumentException($message, 401);
            }

            // Find board
            $board = $this->boardRepository->findOneBy(['id' => $board_id]);

            if (!$board) {
                $message = sprintf('Could not find a board with id %s', $board_id);
                throw new \InvalidArgumentException($message, 401);
            }

            $complaintCategories = $board->getComplaintCategories();

            // Create array with complaint category names as key and value
            $response = [];

            foreach ($complaintCategories as $category) {
                $response[] = ['key' => $category->getName(), 'value' => $category->getName()];
            }

            return new JsonResponse($response);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
    }
}
