<?php

namespace Tests;

use App\Model\StudyCard;
use PHPUnit\Framework\TestCase;

class StudyCardTest extends TestCase
{
    private StudyCard $studyCard;

    protected function setUp(): void
    {
        $this->studyCard = new StudyCard(
            user_id: 1,
            question: 'Co to jest PHP?',
            answer: 'PHP to język programowania po stronie serwera',
            id: 10,
            folder_id: 5,
            created_at: '2024-01-10 12:00:00'
        );
    }

    public function testStudyCardCanBeCreated(): void
    {
        $this->assertEquals('Co to jest PHP?', $this->studyCard->getQuestion());
        $this->assertEquals('PHP to język programowania po stronie serwera', $this->studyCard->getAnswer());
        $this->assertEquals(1, $this->studyCard->getUserId());
    }

    public function testStudyCardHasCorrectId(): void
    {
        $this->assertEquals(10, $this->studyCard->getId());
    }

    public function testStudyCardCanBelongToFolder(): void
    {
        $this->assertNotNull($this->studyCard->getFolderId());
        $this->assertEquals(5, $this->studyCard->getFolderId());
    }

    public function testStudyCardWithoutFolderId(): void
    {
        $cardWithoutFolder = new StudyCard(
            user_id: 2,
            question: 'Pytanie bez folderu?',
            answer: 'Odpowiedź bez folderu'
        );

        $this->assertNull($cardWithoutFolder->getFolderId());
    }

    public function testStudyCardCreatedAtIsSet(): void
    {
        $this->assertNotNull($this->studyCard->getCreatedAt());
        $this->assertEquals('2024-01-10 12:00:00', $this->studyCard->getCreatedAt());
    }
}
