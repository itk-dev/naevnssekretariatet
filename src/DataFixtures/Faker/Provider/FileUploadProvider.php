<?php

namespace App\DataFixtures\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class FileUploadProvider extends Base
{
    public function __construct(Generator $generator, private ContainerInterface $container, private PropertyMappingFactory $propertyMappingFactory, private Filesystem $filesystem)
    {
        parent::__construct($generator);
    }

    /**
     * Simulate file upload using VichUploader.
     *
     * @param string $path      the file path relative to the fixtures directory
     * @param string $property  the object property
     * @param string $className the object class name
     *
     * @return string the file path relative to the configured upload destination
     */
    public function uploadFile(string $path, string $property, string $className)
    {
        $mapping = $this->propertyMappingFactory->fromField([], $property, $className);
        $mapping->getUploadDestination();

        $path = ltrim($path, '/');
        $sourcePath = $this->container->getParameter('kernel.project_dir').'/fixtures/'.$path;
        $targetPath = $mapping->getUploadDestination().'/'.$path;

        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException(sprintf('File source path %s does not exist', $sourcePath));
        }
        $this->filesystem->copy($sourcePath, $targetPath);

        return $path;
    }

    /**
     * Simulate file upload by a simple copy operation.
     *
     * @param string $path        the file path relative to the fixtures directory
     * @param string $destination the destination path. Can be a directory or a filename.
     *
     * @return string the name of the file
     */
    public function copyFile(string $path, string $destination)
    {
        $path = ltrim($path, '/');
        $sourcePath = $this->container->getParameter('kernel.project_dir').'/fixtures/'.$path;

        $ext = pathinfo($destination, PATHINFO_EXTENSION);
        if (empty($ext)) {
            // $destination does not look like a directory path (i.e. it has no extension)
            $destination = rtrim($destination, '/').'/'.basename($sourcePath);
        }
        $targetPath = $this->container->getParameter('kernel.project_dir').'/'.trim($destination);

        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException(sprintf('File source path %s does not exist', $sourcePath));
        }
        $this->filesystem->copy($sourcePath, $targetPath);

        return basename($targetPath);
    }
}
