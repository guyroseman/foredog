<?php
declare(strict_types=1);

class Survey {
    public static function recommend(array $answers): array {
        $scores = [
            'german-shepherd' => 0, 
            'golden-retriever' => 0, 
            'labrador-retriever' => 0, 
            'french-bulldog' => 0
        ];
        
        $activity = $answers['activity_level'] ?? '';
        if ($activity === 'active')        { $scores['german-shepherd'] += 3; $scores['labrador-retriever'] += 2; $scores['golden-retriever'] += 2; }
        elseif ($activity === 'moderate')  { $scores['golden-retriever'] += 3; $scores['labrador-retriever'] += 2; }
        else                               { $scores['french-bulldog'] += 3; }
        
        $space = $answers['living_space'] ?? '';
        if ($space === 'apartment' || $space === 'house') { $scores['french-bulldog'] += 3; $scores['golden-retriever'] += 1; }
        else { $scores['german-shepherd'] += 2; $scores['labrador-retriever'] += 2; }
        
        $exp = $answers['experience'] ?? '';
        if ($exp === 'first')              { $scores['golden-retriever'] += 3; $scores['labrador-retriever'] += 2; }
        elseif ($exp === 'some')           { $scores['labrador-retriever'] += 2; $scores['golden-retriever'] += 1; }
        else                               { $scores['german-shepherd'] += 3; }
        
        arsort($scores);
        // Return the top 3 breeds as an array
        return array_keys(array_slice($scores, 0, 3, true));
    }
}