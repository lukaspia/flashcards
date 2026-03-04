<?php

namespace App\Tests\Security\Voter;

use App\Entity\Lesson;
use App\Entity\User;
use App\Entity\Word;
use App\Security\Voter\WordVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WordVoterTest extends TestCase
{
    private WordVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new WordVoter();
    }

    /**
     * @dataProvider wordVoterDataProvider
     */
    public function testVote(string $attribute, bool $isOwner, int $expectedVote): void
    {
        $owner = $this->createMock(User::class);
        $stranger = $this->createMock(User::class);
        $currentUser = $isOwner ? $owner : $stranger;

        $lesson = $this->createMock(Lesson::class);
        $lesson->method('getUser')->willReturn($owner);

        $word = $this->createMock(Word::class);
        $word->method('getLesson')->willReturn($lesson);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($currentUser);

        $result = $this->voter->vote($token, $word, [$attribute]);

        $this->assertEquals($expectedVote, $result);
    }

    public function wordVoterDataProvider(): array
    {
        return [
            'owner can upload image' => [WordVoter::UPLOAD_IMAGE, true, VoterInterface::ACCESS_GRANTED],
            'owner can delete image' => [WordVoter::DELETE_IMAGE, true, VoterInterface::ACCESS_GRANTED],
            'stranger cannot upload image' => [WordVoter::UPLOAD_IMAGE, false, VoterInterface::ACCESS_DENIED],
            'stranger cannot delete image' => [WordVoter::DELETE_IMAGE, false, VoterInterface::ACCESS_DENIED],
        ];
    }

    public function testVoteAbstainsOnWrongSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $notAWord = new \stdClass();

        $result = $this->voter->vote($token, $notAWord, [WordVoter::UPLOAD_IMAGE]);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}