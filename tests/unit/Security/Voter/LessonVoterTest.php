<?php

namespace App\Tests\Security\Voter;

use App\Entity\Lesson;
use App\Entity\User;
use App\Security\Voter\LessonVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class LessonVoterTest extends TestCase
{
    private function createToken(?User $user = null): UsernamePasswordToken
    {
        return new UsernamePasswordToken(
            $user ?? new User(),
            'main',
            $user ? $user->getRoles() : []
        );
    }

    private function createLesson(?User $owner = null): Lesson
    {
        $lesson = new Lesson();
        if ($owner) {
            $lesson->setUser($owner);
        }
        return $lesson;
    }

    public function testVoteWithValidAttributesAndSubject(): void
    {
        $owner = new User();
        $voter = new LessonVoter();
        $lesson = $this->createLesson($owner);
        $token = $this->createToken($owner);

        // Test VIEW, EDIT, and DELETE attributes
        $this->assertSame(
            Voter::ACCESS_GRANTED,
            $voter->vote($token, $lesson, [LessonVoter::VIEW])
        );
        $this->assertSame(
            Voter::ACCESS_GRANTED,
            $voter->vote($token, $lesson, [LessonVoter::EDIT])
        );
        $this->assertSame(
            Voter::ACCESS_GRANTED,
            $voter->vote($token, $lesson, [LessonVoter::DELETE])
        );
    }

    public function testVoteWithInvalidSubject(): void
    {
        $voter = new LessonVoter();
        $token = $this->createToken(new User());
        
        $this->assertSame(
            Voter::ACCESS_ABSTAIN,
            $voter->vote($token, new \stdClass(), [LessonVoter::VIEW])
        );
    }

    public function testVoteWithInvalidAttribute(): void
    {
        $voter = new LessonVoter();
        $user = new User();
        $lesson = $this->createLesson($user);
        $token = $this->createToken($user);

        $this->assertSame(
            Voter::ACCESS_ABSTAIN,
            $voter->vote($token, $lesson, ['INVALID_ATTRIBUTE'])
        );
    }

    public function testViewAccessGrantedToOwner(): void
    {
        $owner = new User();
        $voter = new LessonVoter();
        $lesson = $this->createLesson($owner);
        $token = $this->createToken($owner);

        $this->assertSame(
            Voter::ACCESS_GRANTED,
            $voter->vote($token, $lesson, [LessonVoter::VIEW])
        );
    }

    public function testViewAccessDeniedToNonOwner(): void
    {
        $owner = new User();
        $otherUser = new User();
        $voter = new LessonVoter();
        $lesson = $this->createLesson($owner);
        $token = $this->createToken($otherUser);

        $this->assertSame(
            Voter::ACCESS_DENIED,
            $voter->vote($token, $lesson, [LessonVoter::VIEW])
        );
    }

    public function testViewAccessDeniedToUnauthenticatedUser(): void
    {
        $voter = new LessonVoter();
        $lesson = $this->createLesson(new User());
        $token = $this->createToken(); // No user

        $this->assertSame(
            Voter::ACCESS_DENIED,
            $voter->vote($token, $lesson, [LessonVoter::VIEW])
        );
    }

    public function testEditAccessGrantedToOwner(): void
    {
        $owner = new User();
        $voter = new LessonVoter();
        $lesson = $this->createLesson($owner);
        $token = $this->createToken($owner);

        $this->assertSame(
            Voter::ACCESS_GRANTED,
            $voter->vote($token, $lesson, [LessonVoter::EDIT])
        );
    }

    public function testDeleteAccessGrantedToOwner(): void
    {
        $owner = new User();
        $voter = new LessonVoter();
        $lesson = $this->createLesson($owner);
        $token = $this->createToken($owner);

        $this->assertSame(
            Voter::ACCESS_GRANTED,
            $voter->vote($token, $lesson, [LessonVoter::DELETE])
        );
    }

    public function testVoteWithNullOwner(): void
    {
        $voter = new LessonVoter();
        
        // Create a User object that will be treated as "no owner"
        $noOwnerUser = new class extends User {
            public function __construct() {
                // Prevent parent constructor from running
            }
            public function getId(): ?int {
                return null;
            }
        };
        
        // Create a lesson with the special no-owner user
        $lesson = $this->createMock(Lesson::class);
        $lesson->method('getUser')
            ->willReturn(new $noOwnerUser());
            
        $user = new User();
        $token = $this->createToken($user);

        $this->assertSame(
            Voter::ACCESS_DENIED,
            $voter->vote($token, $lesson, [LessonVoter::VIEW])
        );
    }
}
