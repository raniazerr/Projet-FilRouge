<?php

namespace App\Tests\Controller;

use App\Controller\RegistrationController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationControllerTest extends TestCase
{
    private function buildDependencies(): array
    {
        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $userRepository = $this->createMock(UserRepository::class);

        return [$hasher, $entityManager, $userRepository];
    }

    public function testRegisterReturnsBadRequestOnInvalidJson(): void
    {
        [$hasher, $entityManager, $userRepository] = $this->buildDependencies();
        $userRepository->method('findOneBy')->willReturn(null);

        $controller = new RegistrationController();
        $request = new Request([], [], [], [], [], [], 'not valid json');

        $response = $controller->register($request, $hasher, $entityManager, $userRepository);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('Invalid JSON', (string) $response->getContent());
    }

    public function testRegisterReturnsBadRequestWhenRequiredFieldsAreMissing(): void
    {
        [$hasher, $entityManager, $userRepository] = $this->buildDependencies();
        $userRepository->method('findOneBy')->willReturn(null);

        $controller = new RegistrationController();
        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'test@example.com',
            // password, nom, prenom manquants
        ]));

        $response = $controller->register($request, $hasher, $entityManager, $userRepository);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('requis', (string) $response->getContent());
    }

    public function testRegisterReturnsBadRequestWhenEmailAlreadyUsed(): void
    {
        [$hasher, $entityManager, $userRepository] = $this->buildDependencies();
        $existingUser = new User();
        $userRepository->method('findOneBy')->willReturn($existingUser);

        $controller = new RegistrationController();
        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'deja.utilise@example.com',
            'password' => 'motdepasse',
            'nom' => 'Parker',
            'prenom' => 'Peter',
        ]));

        $response = $controller->register($request, $hasher, $entityManager, $userRepository);
        $data = json_decode((string) $response->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('utilisé', $data['error']);
    }

    public function testRegisterCreatesUserAndReturns201OnSuccess(): void
    {
        [$hasher, $entityManager, $userRepository] = $this->buildDependencies();
        $userRepository->method('findOneBy')->willReturn(null);

        $entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->once())->method('flush');

        $controller = new RegistrationController();
        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'spidey@example.com',
            'password' => 'motdepasse',
            'nom' => 'Parker',
            'prenom' => 'Peter',
        ]));

        $response = $controller->register($request, $hasher, $entityManager, $userRepository);
        $data = json_decode((string) $response->getContent(), true);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame('spidey@example.com', $data['email']);
        $this->assertSame('Parker', $data['nom']);
        $this->assertSame('Peter', $data['prenom']);
        $this->assertContains('ROLE_USER', $data['roles']);
    }
}
