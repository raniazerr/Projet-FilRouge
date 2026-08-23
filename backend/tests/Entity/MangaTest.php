<?php

namespace App\Tests\Entity;

use App\Entity\Favori;
use App\Entity\Manga;
use App\Entity\Tome;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class MangaTest extends TestCase
{
    public function testCollectionsAreInitializedEmpty(): void
    {
        $manga = new Manga();

        $this->assertInstanceOf(Collection::class, $manga->getTomes());
        $this->assertInstanceOf(Collection::class, $manga->getFavoris());
        $this->assertCount(0, $manga->getTomes());
        $this->assertCount(0, $manga->getFavoris());
    }

    public function testBasicGettersAndSetters(): void
    {
        $manga = new Manga();
        $manga->setApiId(42);
        $manga->setTitre('One Piece');
        $manga->setImage('https://example.com/image.jpg');
        $manga->setSynopsis('Une aventure de pirates.');
        $manga->setGenres(['Aventure', 'Action']);

        $this->assertSame(42, $manga->getApiId());
        $this->assertSame('One Piece', $manga->getTitre());
        $this->assertSame('https://example.com/image.jpg', $manga->getImage());
        $this->assertSame('Une aventure de pirates.', $manga->getSynopsis());
        $this->assertSame(['Aventure', 'Action'], $manga->getGenres());
    }

    public function testSynopsisCanBeNull(): void
    {
        $manga = new Manga();
        $manga->setSynopsis(null);

        $this->assertNull($manga->getSynopsis());
    }

    public function testAddAndRemoveTomeSetsBackReference(): void
    {
        $manga = new Manga();
        $tome = new Tome();

        $manga->addTome($tome);

        $this->assertCount(1, $manga->getTomes());
        $this->assertSame($manga, $tome->getManga());

        $manga->removeTome($tome);

        $this->assertCount(0, $manga->getTomes());
        $this->assertNull($tome->getManga());
    }

    public function testAddTomeTwiceDoesNotDuplicate(): void
    {
        $manga = new Manga();
        $tome = new Tome();

        $manga->addTome($tome);
        $manga->addTome($tome);

        $this->assertCount(1, $manga->getTomes());
    }

    public function testAddAndRemoveFavoriSetsBackReference(): void
    {
        $manga = new Manga();
        $favori = new Favori();

        $manga->addFavori($favori);

        $this->assertCount(1, $manga->getFavoris());
        $this->assertSame($manga, $favori->getManga());

        $manga->removeFavori($favori);

        $this->assertCount(0, $manga->getFavoris());
        $this->assertNull($favori->getManga());
    }
}
