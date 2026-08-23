<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Investment Account IDs
    |--------------------------------------------------------------------------
    |
    | FinanceInvestment IDs classified as "investment" accounts (vs. liquid
    | cash) on the Money Management dashboard. This is a hardcoded-ID heuristic
    | carried over from the original controllers — fragile, but centralizing it
    | here at least stops it from diverging between web and API again (it had:
    | web used [1,2,8,9,11], API used [1,2,8,9] — missing id 11, "AJAIB", a real
    | stock-investment platform). Ideally this becomes a real
    | `finance_investments.is_investment_account` column instead of an ID list;
    | that schema change is out of scope for this refactor.
    | See docs/money-management-refactor.md, bug fix #2.
    |
    */
    'investment_account_ids' => [1, 2, 8, 9, 11],

];
