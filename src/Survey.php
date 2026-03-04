<?php
declare(strict_types=1);

class Survey {
    public static function recommend(array $answers): array {
        // Initialize the 20 most popular global breeds
        $scores = [
            'labrador-retriever' => 0, 'french-bulldog' => 0, 'golden-retriever' => 0,
            'german-shepherd' => 0, 'poodle' => 0, 'bulldog' => 0, 'beagle' => 0,
            'rottweiler' => 0, 'dachshund' => 0, 'corgi' => 0, 'australian-shepherd' => 0,
            'yorkshire-terrier' => 0, 'cavalier-spaniel' => 0, 'doberman' => 0,
            'boxer' => 0, 'miniature-schnauzer' => 0, 'shih-tzu' => 0,
            'siberian-husky' => 0, 'pug' => 0, 'border-collie' => 0
        ];

        // Evaluate Q1: Activity Level
        $activity = $answers['activity_level'] ?? '';
        if ($activity === 'active') {
            $activeBreeds = ['german-shepherd', 'labrador-retriever', 'golden-retriever', 'rottweiler', 'australian-shepherd', 'doberman', 'boxer', 'siberian-husky', 'border-collie'];
            foreach($activeBreeds as $b) $scores[$b] += 3;
        } elseif ($activity === 'moderate') {
            $modBreeds = ['labrador-retriever', 'golden-retriever', 'poodle', 'beagle', 'corgi', 'miniature-schnauzer', 'boxer'];
            foreach($modBreeds as $b) $scores[$b] += 3;
        } else { // low
            $lowBreeds = ['french-bulldog', 'bulldog', 'dachshund', 'yorkshire-terrier', 'cavalier-spaniel', 'shih-tzu', 'pug'];
            foreach($lowBreeds as $b) $scores[$b] += 3;
        }

        // Evaluate Q2: Living Space
        $space = $answers['living_space'] ?? '';
        if ($space === 'apartment') {
            $aptBreeds = ['french-bulldog', 'poodle', 'bulldog', 'dachshund', 'corgi', 'yorkshire-terrier', 'cavalier-spaniel', 'miniature-schnauzer', 'shih-tzu', 'pug'];
            foreach($aptBreeds as $b) $scores[$b] += 3;
        } else { // House
            $houseBreeds = ['labrador-retriever', 'golden-retriever', 'german-shepherd', 'beagle', 'rottweiler', 'australian-shepherd', 'doberman', 'boxer', 'siberian-husky', 'border-collie'];
            foreach($houseBreeds as $b) $scores[$b] += 2;
        }

        // Evaluate Q3: Desired Trait
        $trait = $answers['trait'] ?? '';
        if ($trait === 'experienced') { // Loyal Guard
            $guardBreeds = ['german-shepherd', 'rottweiler', 'doberman', 'boxer', 'siberian-husky', 'dachshund'];
            foreach($guardBreeds as $b) $scores[$b] += 3;
        } elseif ($trait === 'some') { // Cuddle Bug
            $cuddleBreeds = ['golden-retriever', 'french-bulldog', 'bulldog', 'cavalier-spaniel', 'shih-tzu', 'pug', 'labrador-retriever', 'beagle', 'yorkshire-terrier'];
            foreach($cuddleBreeds as $b) $scores[$b] += 3;
        } else { // Trainable
            $trainBreeds = ['poodle', 'german-shepherd', 'golden-retriever', 'labrador-retriever', 'corgi', 'australian-shepherd', 'miniature-schnauzer', 'border-collie'];
            foreach($trainBreeds as $b) $scores[$b] += 3;
        }

        // Evaluate Q4: Experience
        $exp = $answers['experience'] ?? '';
        if ($exp === 'first') {
            $firstBreeds = ['golden-retriever', 'labrador-retriever', 'poodle', 'french-bulldog', 'pug', 'cavalier-spaniel', 'shih-tzu', 'yorkshire-terrier'];
            foreach($firstBreeds as $b) $scores[$b] += 3;
        } elseif ($exp === 'some') {
            $someBreeds = ['beagle', 'bulldog', 'dachshund', 'corgi', 'miniature-schnauzer', 'boxer'];
            foreach($someBreeds as $b) $scores[$b] += 2;
            foreach(['golden-retriever', 'labrador-retriever'] as $b) $scores[$b] += 1;
        } else { // experienced
            $expBreeds = ['german-shepherd', 'rottweiler', 'australian-shepherd', 'doberman', 'siberian-husky', 'border-collie'];
            foreach($expBreeds as $b) $scores[$b] += 3;
        }

        // Tie-breaker: Shuffle the array to ensure dynamic results for matching scores
        $keys = array_keys($scores);
        shuffle($keys);
        $shuffledScores = [];
        foreach ($keys as $key) {
            $shuffledScores[$key] = $scores[$key];
        }
        
        // Sort array in descending order based on score values
        arsort($shuffledScores);
        
        // Return the top 3 dynamic breeds
        return array_keys(array_slice($shuffledScores, 0, 3, true));
    }
}