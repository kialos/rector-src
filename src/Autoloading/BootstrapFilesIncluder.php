<?php

declare(strict_types=1);

namespace Rector\Core\Autoloading;

use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\FileSystem\FilesFinder;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;
use Webmozart\Assert\Assert;

final class BootstrapFilesIncluder
{
    public function __construct(
        private readonly ParameterProvider $parameterProvider,
        private readonly FilesFinder $filesFinder
    ) {
    }

    /**
     * Inspired by
     * @see https://github.com/phpstan/phpstan-src/commit/aad1bf888ab7b5808898ee5fe2228bb8bb4e4cf1
     */
    public function includeBootstrapFiles(): void
    {
        $bootstrapFiles = $this->parameterProvider->provideArrayParameter(Option::BOOTSTRAP_FILES);

        Assert::allString($bootstrapFiles);

        /** @var string[] $bootstrapFiles */
        foreach ($bootstrapFiles as $bootstrapFile) {
            if (! is_file($bootstrapFile)) {
                throw new ShouldNotHappenException(sprintf('Bootstrap file "%s" does not exist.', $bootstrapFile));
            }

            try {
                require_once $bootstrapFile;
            } catch (Throwable $throwable) {
                $errorMessage = sprintf(
                    '"%s" thrown in "%s" on line %d while loading bootstrap file %s: %s',
                    $throwable::class,
                    $throwable->getFile(),
                    $throwable->getLine(),
                    $bootstrapFile,
                    $throwable->getMessage()
                );

                throw new ShouldNotHappenException($errorMessage, $throwable->getCode(), $throwable);
            }
        }

        $stubsRectorDirectory = realpath(__DIR__ . '/../../stubs-rector');
        if ($stubsRectorDirectory === false) {
            return;
        }

        $stubs = $this->filesFinder->findInDirectoriesAndFiles([$stubsRectorDirectory], ['php']);
        foreach ($stubs as $stub) {
            require_once $stub->getRealPath();
        }
    }
}
