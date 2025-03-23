<?php
declare(strict_types=1);

class Dragwyb_Advanced_Operators extends Dragwyb_Conditional_Operators {
    /**
     * Get all advanced operators
     */
    public function get_advanced_operators(): array {
        return array_merge(
            $this->get_mathematical_operators(),
            $this->get_time_operators(),
            $this->get_geo_operators(),
            $this->get_validation_operators(),
            $this->get_calculation_operators(),
            $this->get_string_manipulation_operators()
        );
    }

    /**
     * Mathematical operators
     */
    private function get_mathematical_operators(): array {
        return [
            'mathematical' => [
                'label' => __('Mathematical', 'dragwyb-form-builder'),
                'operators' => [
                    'sum_equals' => __('Sum Equals', 'dragwyb-form-builder'),
                    'average_equals' => __('Average Equals', 'dragwyb-form-builder'),
                    'product_equals' => __('Product Equals', 'dragwyb-form-builder'),
                    'ratio_equals' => __('Ratio Equals', 'dragwyb-form-builder'),
                    'percentage_of' => __('Percentage Of', 'dragwyb-form-builder'),
                    'is_prime' => __('Is Prime Number', 'dragwyb-form-builder'),
                    'is_even' => __('Is Even Number', 'dragwyb-form-builder'),
                    'is_odd' => __('Is Odd Number', 'dragwyb-form-builder'),
                    'round_to' => __('Rounds To', 'dragwyb-form-builder'),
                    'factorial_equals' => __('Factorial Equals', 'dragwyb-form-builder'),
                ],
                'value_type' => 'number',
                'callback' => [$this, 'evaluate_mathematical'],
            ],
        ];
    }

    /**
     * Time operators
     */
    private function get_time_operators(): array {
        return [
            'time' => [
                'label' => __('Time', 'dragwyb-form-builder'),
                'operators' => [
                    'time_between' => __('Time Between', 'dragwyb-form-builder'),
                    'business_hours' => __('During Business Hours', 'dragwyb-form-builder'),
                    'time_difference' => __('Time Difference', 'dragwyb-form-builder'),
                    'is_holiday' => __('Is Holiday', 'dragwyb-form-builder'),
                    'is_business_day' => __('Is Business Day', 'dragwyb-form-builder'),
                    'quarter_equals' => __('Quarter Equals', 'dragwyb-form-builder'),
                    'season_equals' => __('Season Equals', 'dragwyb-form-builder'),
                    'timezone_matches' => __('Timezone Matches', 'dragwyb-form-builder'),
                    'day_of_week' => __('Day of Week', 'dragwyb-form-builder'),
                    'week_of_month' => __('Week of Month', 'dragwyb-form-builder'),
                ],
                'value_type' => 'time',
                'callback' => [$this, 'evaluate_time'],
            ],
        ];
    }

    /**
     * Geographical operators
     */
    private function get_geo_operators(): array {
        return [
            'geographical' => [
                'label' => __('Geographical', 'dragwyb-form-builder'),
                'operators' => [
                    'distance_within' => __('Distance Within', 'dragwyb-form-builder'),
                    'country_equals' => __('Country Equals', 'dragwyb-form-builder'),
                    'region_equals' => __('Region Equals', 'dragwyb-form-builder'),
                    'city_equals' => __('City Equals', 'dragwyb-form-builder'),
                    'postal_code_matches' => __('Postal Code Matches', 'dragwyb-form-builder'),
                    'continent_equals' => __('Continent Equals', 'dragwyb-form-builder'),
                    'timezone_in' => __('Timezone In', 'dragwyb-form-builder'),
                    'coordinates_within' => __('Coordinates Within', 'dragwyb-form-builder'),
                    'is_location_type' => __('Is Location Type', 'dragwyb-form-builder'),
                    'address_contains' => __('Address Contains', 'dragwyb-form-builder'),
                ],
                'value_type' => 'geo',
                'callback' => [$this, 'evaluate_geographical'],
            ],
        ];
    }

    /**
     * Validation operators
     */
    private function get_validation_operators(): array {
        return [
            'validation' => [
                'label' => __('Validation', 'dragwyb-form-builder'),
                'operators' => [
                    'is_valid_email' => __('Is Valid Email', 'dragwyb-form-builder'),
                    'is_valid_url' => __('Is Valid URL', 'dragwyb-form-builder'),
                    'is_valid_ip' => __('Is Valid IP', 'dragwyb-form-builder'),
                    'is_valid_credit_card' => __('Is Valid Credit Card', 'dragwyb-form-builder'),
                    'matches_format' => __('Matches Format', 'dragwyb-form-builder'),
                    'has_valid_checksum' => __('Has Valid Checksum', 'dragwyb-form-builder'),
                    'passes_validation' => __('Passes Validation', 'dragwyb-form-builder'),
                    'meets_requirements' => __('Meets Requirements', 'dragwyb-form-builder'),
                    'is_unique_in' => __('Is Unique In', 'dragwyb-form-builder'),
                    'follows_pattern' => __('Follows Pattern', 'dragwyb-form-builder'),
                ],
                'value_type' => 'validation',
                'callback' => [$this, 'evaluate_validation'],
            ],
        ];
    }

    /**
     * Calculation operators
     */
    private function get_calculation_operators(): array {
        return [
            'calculation' => [
                'label' => __('Calculation', 'dragwyb-form-builder'),
                'operators' => [
                    'formula_equals' => __('Formula Equals', 'dragwyb-form-builder'),
                    'calculate_bmi' => __('Calculate BMI', 'dragwyb-form-builder'),
                    'calculate_age' => __('Calculate Age', 'dragwyb-form-builder'),
                    'calculate_discount' => __('Calculate Discount', 'dragwyb-form-builder'),
                    'calculate_tax' => __('Calculate Tax', 'dragwyb-form-builder'),
                    'calculate_interest' => __('Calculate Interest', 'dragwyb-form-builder'),
                    'calculate_percentage' => __('Calculate Percentage', 'dragwyb-form-builder'),
                    'calculate_average' => __('Calculate Average', 'dragwyb-form-builder'),
                    'calculate_ratio' => __('Calculate Ratio', 'dragwyb-form-builder'),
                    'calculate_difference' => __('Calculate Difference', 'dragwyb-form-builder'),
                ],
                'value_type' => 'calculation',
                'callback' => [$this, 'evaluate_calculation'],
            ],
        ];
    }

    /**
     * String manipulation operators
     */
    private function get_string_manipulation_operators(): array {
        return [
            'string_manipulation' => [
                'label' => __('String Manipulation', 'dragwyb-form-builder'),
                'operators' => [
                    'transform_case' => __('Transform Case', 'dragwyb-form-builder'),
                    'extract_substring' => __('Extract Substring', 'dragwyb-form-builder'),
                    'replace_text' => __('Replace Text', 'dragwyb-form-builder'),
                    'concatenate_with' => __('Concatenate With', 'dragwyb-form-builder'),
                    'trim_spaces' => __('Trim Spaces', 'dragwyb-form-builder'),
                    'remove_characters' => __('Remove Characters', 'dragwyb-form-builder'),
                    'format_number' => __('Format Number', 'dragwyb-form-builder'),
                    'format_date' => __('Format Date', 'dragwyb-form-builder'),
                    'format_currency' => __('Format Currency', 'dragwyb-form-builder'),
                    'format_phone' => __('Format Phone', 'dragwyb-form-builder'),
                ],
                'value_type' => 'string',
                'callback' => [$this, 'evaluate_string_manipulation'],
            ],
        ];
    }

    /**
     * Evaluate mathematical operators
     */
    public function evaluate_mathematical($value, $operator, $compare_value): bool {
        switch ($operator) {
            case 'sum_equals':
                if (!is_array($value)) return false;
                return array_sum($value) === (float) $compare_value;

            case 'average_equals':
                if (!is_array($value)) return false;
                return (array_sum($value) / count($value)) === (float) $compare_value;

            case 'product_equals':
                if (!is_array($value)) return false;
                return array_product($value) === (float) $compare_value;

            case 'ratio_equals':
                if (!is_array($value) || count($value) !== 2) return false;
                return ($value[0] / $value[1]) === (float) $compare_value;

            case 'percentage_of':
                if (!is_array($compare_value) || count($compare_value) !== 2) return false;
                return ($value / $compare_value[0]) * 100 === (float) $compare_value[1];

            case 'is_prime':
                if ($value < 2) return false;
                for ($i = 2; $i <= sqrt($value); $i++) {
                    if ($value % $i === 0) return false;
                }
                return true;

            case 'is_even':
                return $value % 2 === 0;

            case 'is_odd':
                return $value % 2 !== 0;

            case 'round_to':
                return round($value, (int) $compare_value[1]) === (float) $compare_value[0];

            case 'factorial_equals':
                $factorial = 1;
                for ($i = 1; $i <= $value; $i++) {
                    $factorial *= $i;
                }
                return $factorial === (int) $compare_value;

            default:
                return false;
        }
    }

    /**
     * Evaluate time operators
     */
    public function evaluate_time($value, $operator, $compare_value): bool {
        $time = strtotime($value);
        if ($time === false) return false;

        switch ($operator) {
            case 'time_between':
                if (!is_array($compare_value) || count($compare_value) !== 2) return false;
                $start = strtotime($compare_value[0]);
                $end = strtotime($compare_value[1]);
                return $time >= $start && $time <= $end;

            case 'business_hours':
                $hour = (int) date('G', $time);
                return $hour >= 9 && $hour < 17;

            case 'time_difference':
                if (!is_array($compare_value) || count($compare_value) !== 2) return false;
                $compare_time = strtotime($compare_value[0]);
                $difference = abs($time - $compare_time);
                return $difference === (int) $compare_value[1];

            case 'is_holiday':
                // Implementation would require a holiday calendar
                return false;

            case 'is_business_day':
                $day = (int) date('N', $time);
                return $day >= 1 && $day <= 5;

            case 'quarter_equals':
                $quarter = ceil(date('n', $time) / 3);
                return $quarter === (int) $compare_value;

            case 'season_equals':
                $month = (int) date('n', $time);
                $seasons = [
                    'winter' => [12, 1, 2],
                    'spring' => [3, 4, 5],
                    'summer' => [6, 7, 8],
                    'fall' => [9, 10, 11],
                ];
                return in_array($month, $seasons[$compare_value]);

            case 'timezone_matches':
                $timezone = date_default_timezone_get();
                return $timezone === $compare_value;

            case 'day_of_week':
                return date('l', $time) === $compare_value;

            case 'week_of_month':
                return ceil(date('j', $time) / 7) === (int) $compare_value;

            default:
                return false;
        }
    }

    /**
     * Evaluate geographical operators
     */
    public function evaluate_geographical($value, $operator, $compare_value): bool {
        switch ($operator) {
            case 'distance_within':
                if (!is_array($value) || !is_array($compare_value)) return false;
                return $this->calculate_distance(
                    $value['lat'], 
                    $value['lng'], 
                    $compare_value['lat'], 
                    $compare_value['lng']
                ) <= $compare_value['radius'];

            // Additional geographical operator implementations...
            // These would require integration with geocoding services
            // and geographical databases for full functionality

            default:
                return false;
        }
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculate_distance($lat1, $lon1, $lat2, $lon2): float {
        $radius = 6371; // Earth's radius in kilometers

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $radius * $c;
    }

    /**
     * Evaluate validation operators
     */
    public function evaluate_validation($value, $operator, $compare_value): bool {
        switch ($operator) {
            case 'is_valid_email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'is_valid_url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'is_valid_ip':
                return filter_var($value, FILTER_VALIDATE_IP) !== false;

            case 'is_valid_credit_card':
                return $this->validate_credit_card($value);

            case 'matches_format':
                return preg_match($compare_value, $value) === 1;

            case 'has_valid_checksum':
                return $this->validate_checksum($value, $compare_value);

            // Additional validation operator implementations...

            default:
                return false;
        }
    }

    /**
     * Evaluate calculation operators
     */
    public function evaluate_calculation($value, $operator, $compare_value): bool {
        switch ($operator) {
            case 'formula_equals':
                // Safely evaluate mathematical formula
                $formula = str_replace('x', $value, $compare_value);
                return $this->evaluate_formula($formula);

            case 'calculate_bmi':
                if (!is_array($value)) return false;
                $bmi = ($value['weight'] / pow($value['height'], 2)) * 10000;
                return $bmi === (float) $compare_value;

            case 'calculate_age':
                $birth = new DateTime($value);
                $now = new DateTime();
                return $birth->diff($now)->y === (int) $compare_value;

            // Additional calculation operator implementations...

            default:
                return false;
        }
    }

    /**
     * Evaluate string manipulation operators
     */
    public function evaluate_string_manipulation($value, $operator, $compare_value): bool {
        switch ($operator) {
            case 'transform_case':
                switch ($compare_value) {
                    case 'upper':
                        return $value === strtoupper($value);
                    case 'lower':
                        return $value === strtolower($value);
                    case 'title':
                        return $value === ucwords($value);
                    default:
                        return false;
                }

            case 'extract_substring':
                if (!is_array($compare_value)) return false;
                $extracted = substr($value, $compare_value[0], $compare_value[1]);
                return $extracted === $compare_value[2];

            case 'replace_text':
                if (!is_array($compare_value)) return false;
                $replaced = str_replace($compare_value[0], $compare_value[1], $value);
                return $replaced === $compare_value[2];

            // Additional string manipulation operator implementations...

            default:
                return false;
        }
    }

    /**
     * Validate credit card number using Luhn algorithm
     */
    private function validate_credit_card(string $number): bool {
        $number = preg_replace('/\D/', '', $number);
        $length = strlen($number);
        $sum = 0;
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$i];
            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * Safely evaluate mathematical formula
     */
    private function evaluate_formula(string $formula): bool {
        // Remove any potentially dangerous characters
        $formula = preg_replace('/[^0-9+\-.*\/()x\s]/', '', $formula);
        
        // Basic security check
        if (preg_match('/[a-zA-Z_]/', $formula)) {
            return false;
        }

        try {
            return eval('return ' . $formula . ';');
        } catch (Exception $e) {
            return false;
        }
    }
} 