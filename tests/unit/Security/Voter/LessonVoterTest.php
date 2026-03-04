<?php

namespace App\Tests\Security\Voter;

use App\Entity\Lesson;
use App\Entity\User;
use App\Security\Voter\LessonVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class LessonVoterTest extends TestCase
{
    private LessonVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new LessonVoter();
    }

    /**
     * @dataProvider voterDataProvider
     */
    public function testVote(string $attribute, bool $isOwner, int $expectedVote): void
    {
        $owner = $this->createMock(User::class);
        $otherUser = $this->createMock(User::class);

        $currentUser = $isOwner ? $owner : $otherUser;

        $lesson = $this->createMock(Lesson::class);
        $lesson->method('getUser')->willReturn($owner);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($currentUser);

        $result = $this->voter->vote($token, $lesson, [$attribute]);

        $this->assertEquals($expectedVote, $result);
    }

    public function voterDataProvider(): array
    {
        return [
            'owner can view' => [LessonVoter::VIEW, true, VoterInterface::ACCESS_GRANTED],
            'owner can edit' => [LessonVoter::EDIT, true, VoterInterface::ACCESS_GRANTED],
            'owner can delete' => [LessonVoter::DELETE, true, VoterInterface::ACCESS_GRANTED],
            'stranger cannot view' => [LessonVoter::VIEW, false, VoterInterface::ACCESS_DENIED],
            'stranger cannot edit' => [LessonVoter::EDIT, false, VoterInterface::ACCESS_DENIED],
        ];
    }

    public function testVoteAbstainsOnUnsupportedAttribute(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $lesson = $this->createMock(Lesson::class);

        $result = $this->voter->vote($token, $lesson, ['SOME_OTHER_ATTRIBUTE']);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testVoteAbstainsOnUnsupportedSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $notALesson = new \stdClass();

        $result = $this->voter->vote($token, $notALesson, [LessonVoter::VIEW]);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}