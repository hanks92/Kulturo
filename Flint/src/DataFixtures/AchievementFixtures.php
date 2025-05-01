<?php

namespace App\DataFixtures;

use App\Entity\Achievement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AchievementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $achievements = [
            ['code' => 'session_complete', 'name' => 'Session Completed', 'desc' => 'Complete a revision session.', 'rewards' => ['xp' => 10], 'premium' => false],
            ['code' => 'first_deck_created', 'name' => 'First Deck Created', 'desc' => 'Create your first flashcard deck.', 'rewards' => ['xp' => 50, 'tree' => 'classic'], 'premium' => false],
            ['code' => '5_day_streak', 'name' => 'Motivated Beginner', 'desc' => 'Study for 5 consecutive days.', 'rewards' => ['xp' => 100, 'tree' => 'classic'], 'premium' => false],
            ['code' => '30_day_streak', 'name' => 'Memory Pillar', 'desc' => 'Study for 30 consecutive days.', 'rewards' => ['xp' => 200, 'tree' => 'fire'], 'premium' => false],
            ['code' => '1000_cards', 'name' => 'Review Master', 'desc' => 'Review 1000 cards in total.', 'rewards' => ['xp' => 150, 'tree' => 'classic'], 'premium' => false],
            ['code' => 'flash_20_correct', 'name' => 'Lightning Flash', 'desc' => 'Get 20 correct answers in under 3 minutes.', 'rewards' => ['xp' => 100, 'tree' => 'electric'], 'premium' => false],
            ['code' => 'perfect_score', 'name' => 'Perfect Score', 'desc' => 'Achieve 100% on a deck.', 'rewards' => ['xp' => 120, 'tree' => 'classic'], 'premium' => false],
            ['code' => 'ai_generated_deck', 'name' => 'AI Explorer', 'desc' => 'Generate a deck using AI.', 'rewards' => ['xp' => 80, 'mushroom' => 1], 'premium' => false],
            ['code' => 'premium_unlocked', 'name' => 'Kulturo Supporter', 'desc' => 'Purchase the premium plan.', 'rewards' => ['special_tree' => true, 'card_designs' => true], 'premium' => true],
            ['code' => 'deck_completed', 'name' => 'Deck Completed', 'desc' => 'Review 100% of a deck.', 'rewards' => ['xp' => 150, 'tree' => 'knowledge'], 'premium' => false],
            ['code' => 'session_100_cards', 'name' => 'Grind Time', 'desc' => 'Review more than 100 cards in a single session.', 'rewards' => ['xp' => 100], 'premium' => false],
            ['code' => 'unlocked_10_plants', 'name' => 'Expert Gardener', 'desc' => 'Unlock 10 plants.', 'rewards' => ['xp' => 90], 'premium' => false],
            ['code' => '5_plant_types', 'name' => 'Balanced Ecosystem', 'desc' => 'Have 5 different plant types in your garden.', 'rewards' => ['xp' => 90], 'premium' => false],
        ];

        foreach ($achievements as $data) {
            $achievement = new Achievement();
            $achievement->setCode($data['code']);
            $achievement->setName($data['name']);
            $achievement->setDescription($data['desc']);
            $achievement->setRewards($data['rewards']);
            $achievement->setIsPremium($data['premium']);

            $manager->persist($achievement);
        }

        $manager->flush();
    }
}
