<?php
/**
 * BugHunter Initial Seed Data
 * Contains comprehensive challenges, languages, and achievements.
 */

return [
    'languages' => [
        [
            'slug' => 'python',
            'name' => 'Python',
            'icon' => 'fa-brands fa-python',
            'color' => '#3776AB',
            'description' => 'Dynamic, high-level language known for clean syntax and versatile ecosystems.'
        ],
        [
            'slug' => 'javascript',
            'name' => 'JavaScript',
            'icon' => 'fa-brands fa-js',
            'color' => '#F7DF1E',
            'description' => 'The language of the modern web, running in browsers, servers, and beyond.'
        ],
        [
            'slug' => 'c',
            'name' => 'C',
            'icon' => 'fa-solid fa-c',
            'color' => '#00599C',
            'description' => 'Foundational low-level systems programming language with manual memory control.'
        ],
        [
            'slug' => 'cpp',
            'name' => 'C++',
            'icon' => 'fa-solid fa-code',
            'color' => '#004482',
            'description' => 'High-performance object-oriented and systems programming language.'
        ],
        [
            'slug' => 'java',
            'name' => 'Java',
            'icon' => 'fa-brands fa-java',
            'color' => '#EA2D2E',
            'description' => 'Robust, class-based object-oriented language running on the JVM.'
        ],
        [
            'slug' => 'php',
            'name' => 'PHP',
            'icon' => 'fa-brands fa-php',
            'color' => '#777BB4',
            'description' => 'Popular server-side scripting language powering web backends and APIs.'
        ],
        [
            'slug' => 'sql',
            'name' => 'SQL',
            'icon' => 'fa-solid fa-database',
            'color' => '#E38C00',
            'description' => 'Standard relational database query language for managing structured data.'
        ],
        [
            'slug' => 'html-css',
            'name' => 'HTML / CSS',
            'icon' => 'fa-brands fa-html5',
            'color' => '#E44D26',
            'description' => 'The essential markup and styling foundation of modern visual interfaces.'
        ],
    ],

    'achievements' => [
        [
            'slug' => 'first_blood',
            'title' => 'First Bug Squashed',
            'description' => 'Successfully resolve your first debugging challenge on BugHunter.',
            'icon' => 'fa-solid fa-bug-slash',
            'badge_color' => '#10B981',
            'xp_bonus' => 50,
            'condition_type' => 'solved_count',
            'condition_value' => 1
        ],
        [
            'slug' => 'streak_trio',
            'title' => 'Hat-Trick Hunter',
            'description' => 'Solve 3 challenges in a row without making any failed attempts.',
            'icon' => 'fa-solid fa-fire',
            'badge_color' => '#F59E0B',
            'xp_bonus' => 100,
            'condition_type' => 'perfect_streak',
            'condition_value' => 3
        ],
        [
            'slug' => 'polyglot',
            'title' => 'Polyglot Debugger',
            'description' => 'Fix bugs in at least 4 different programming languages.',
            'icon' => 'fa-solid fa-earth-americas',
            'badge_color' => '#8B5CF6',
            'xp_bonus' => 150,
            'condition_type' => 'languages_solved',
            'condition_value' => 4
        ],
        [
            'slug' => 'no_hints',
            'title' => 'Eagle Eyes',
            'description' => 'Solve a Hard or Expert challenge without unlocking any hints.',
            'icon' => 'fa-solid fa-eye',
            'badge_color' => '#EC4899',
            'xp_bonus' => 120,
            'condition_type' => 'no_hints_hard',
            'condition_value' => 1
        ],
        [
            'slug' => 'bug_master_10',
            'title' => 'Exterminator Apprentice',
            'description' => 'Squash a total of 10 debugging challenges.',
            'icon' => 'fa-solid fa-medal',
            'badge_color' => '#3B82F6',
            'xp_bonus' => 200,
            'condition_type' => 'solved_count',
            'condition_value' => 10
        ],
        [
            'slug' => 'mix_master',
            'title' => 'Mix Mode Maestro',
            'description' => 'Complete 5 challenges while playing in Mix Mode.',
            'icon' => 'fa-solid fa-shuffle',
            'badge_color' => '#06B6D4',
            'xp_bonus' => 150,
            'condition_type' => 'mix_mode_solved',
            'condition_value' => 5
        ]
    ],

    'challenges' => [
        // ======================== PYTHON ========================
        [
            'title' => 'The Mutable Default Trap',
            'slug' => 'py-mutable-default-trap',
            'language_slug' => 'python',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'logical',
            'description' => 'The function `add_item` is supposed to append an item to a list and return it. However, when called multiple times without providing a list, previously added items unexpectedly persist!',
            'broken_code' => "def add_item(item, shopping_list=[]):\n    shopping_list.append(item)\n    return shopping_list\n\n# Test calls\nprint(add_item('apples'))\nprint(add_item('oranges'))  # BUG: outputs ['apples', 'oranges'] instead of ['oranges']",
            'solution_code' => "def add_item(item, shopping_list=None):\n    if shopping_list is None:\n        shopping_list = []\n    shopping_list.append(item)\n    return shopping_list\n\n# Test calls\nprint(add_item('apples'))\nprint(add_item('oranges'))",
            'error_output' => "Unexpected Output:\n['apples']\n['apples', 'oranges']  <-- Expected: ['oranges'] for second call",
            'expected_behavior' => "When `shopping_list` is not passed in the second call, it should start with a fresh empty list `['oranges']`.",
            'hint1' => "In Python, default arguments are evaluated only once when the function is defined, not every time it is called.",
            'hint2' => "Using a mutable object like a list `[]` as a default parameter causes state to be shared across calls.",
            'hint3' => "Change default to `shopping_list=None` and initialize `if shopping_list is None: shopping_list = []` inside the function.",
            'explanation_bug' => "Python default argument expressions are evaluated once at function definition time, making mutable defaults (like `[]` or `{}`) shared across all invocations.",
            'explanation_why' => "Because `shopping_list=[]` is instantiated only once when Python compiles the function header, every call that relies on the default parameter mutates the same list object in memory.",
            'explanation_fix' => "Use `None` as the sentinel default value and instantiate a new empty list inside the function body whenever the parameter is `None`.",
            'verification_rules' => json_encode([
                'must_contain' => ['shopping_list=None', 'if shopping_list is None', 'shopping_list = []'],
                'must_not_contain' => ['shopping_list=[]']
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'Missing Dictionary Key Crash',
            'slug' => 'py-keyerror-crash',
            'language_slug' => 'python',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'runtime',
            'description' => 'The profile processing script crashes with a `KeyError` when an optional field `vip_status` is missing from the customer data.',
            'broken_code' => "def is_vip_customer(customer):\n    # Crashes if customer dictionary has no 'vip_status' key\n    if customer['vip_status'] == True:\n        return 'VIP Member'\n    return 'Regular Customer'\n\nuser = {'name': 'Alice', 'orders': 3}\nprint(is_vip_customer(user))",
            'solution_code' => "def is_vip_customer(customer):\n    # Safely checks optional key\n    if customer.get('vip_status', False) == True:\n        return 'VIP Member'\n    return 'Regular Customer'\n\nuser = {'name': 'Alice', 'orders': 3}\nprint(is_vip_customer(user))",
            'error_output' => "Traceback (most recent call last):\n  File \"solution.py\", line 3, in is_vip_customer\n    if customer['vip_status'] == True:\nKeyError: 'vip_status'",
            'expected_behavior' => "Safely handle customer records that may not have the 'vip_status' key, defaulting to False/Regular Customer without crashing.",
            'hint1' => "Direct square-bracket index access `dict[key]` raises KeyError if the key is missing.",
            'hint2' => "The dictionary method `.get(key, default)` safely returns a fallback value if the key does not exist.",
            'hint3' => "Replace `customer['vip_status']` with `customer.get('vip_status', False)`.",
            'explanation_bug' => "Accessing a non-existent key in a Python dict with square bracket notation raises an unhandled `KeyError` exception.",
            'explanation_why' => "Python dictionaries do not return `None` or falsey values automatically when indexing missing keys; they raise an exception to prevent silent data issues.",
            'explanation_fix' => "Use `customer.get('vip_status', False)` which returns the specified default value without raising an exception.",
            'verification_rules' => json_encode([
                'must_contain' => [".get('vip_status'"],
                'must_not_contain' => ["customer['vip_status']"]
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'Infinite Recursion Stack Overflow',
            'slug' => 'py-infinite-recursion',
            'language_slug' => 'python',
            'difficulty' => 'medium',
            'level_tier' => 2,
            'bug_type' => 'runtime',
            'description' => 'The recursive function `countdown_timer` is supposed to count down from `n` to `1` and then print "Blast off!", but instead it crashes the interpreter.',
            'broken_code' => "def countdown_timer(n):\n    print(n)\n    # BUG: missing base condition\n    countdown_timer(n - 1)\n\ncountdown_timer(5)",
            'solution_code' => "def countdown_timer(n):\n    if n <= 0:\n        print(\"Blast off!\")\n        return\n    print(n)\n    countdown_timer(n - 1)\n\ncountdown_timer(5)",
            'error_output' => "RecursionError: maximum recursion depth exceeded while calling a Python object",
            'expected_behavior' => "Count down numbers 5, 4, 3, 2, 1 and terminate gracefully with 'Blast off!'.",
            'hint1' => "Every recursive function requires a terminating condition known as a base case.",
            'hint2' => "Without checking if `n <= 0`, the function will recurse infinitely into negative numbers until Python hits its stack limit.",
            'hint3' => "Add `if n <= 0: return` or print and return before the recursive call.",
            'explanation_bug' => "The recursive function lacks a base case, causing it to call itself endlessly until the call stack overflows.",
            'explanation_why' => "Each recursive invocation pushes a frame onto the call stack. Without a stopping condition, the maximum recursion depth (default 1000 in Python) is exceeded.",
            'explanation_fix' => "Add an explicit base case `if n <= 0:` that prints the terminal message and returns immediately.",
            'verification_rules' => json_encode([
                'must_contain' => ['n <= 0', 'return'],
                'must_not_contain' => []
            ]),
            'xp_reward' => 120
        ],

        // ======================== JAVASCRIPT ========================
        [
            'title' => 'Var Scoping in Asynchronous Loop',
            'slug' => 'js-var-scoping-async-loop',
            'language_slug' => 'javascript',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'scoping',
            'description' => 'The developer created a loop to print delayed numbers 0, 1, 2. Instead, the console prints `3, 3, 3`!',
            'broken_code' => "for (var i = 0; i < 3; i++) {\n    setTimeout(() => {\n        console.log(\"Timer value:\", i);\n    }, 100);\n}",
            'solution_code' => "for (let i = 0; i < 3; i++) {\n    setTimeout(() => {\n        console.log(\"Timer value:\", i);\n    }, 100);\n}",
            'error_output' => "Actual Output:\nTimer value: 3\nTimer value: 3\nTimer value: 3\nExpected Output:\nTimer value: 0\nTimer value: 1\nTimer value: 2",
            'expected_behavior' => "Each timer callback should capture its own iteration number (0, 1, 2).",
            'hint1' => "Notice the declaration keyword used for `i` in the `for` loop.",
            'hint2' => "`var` is function-scoped (or globally scoped), meaning all timer callbacks share the exact same variable `i`.",
            'hint3' => "Use `let` which provides block-scoping so each loop iteration creates a new binding for `i`.",
            'explanation_bug' => "Using `var` creates a single shared variable across all loop iterations. When callbacks execute, `i` has already incremented to 3.",
            'explanation_why' => "`var` does not create a block scope inside the loop. By the time `setTimeout` callbacks run asynchronously, the loop has completed and `i` equals 3.",
            'explanation_fix' => "Replace `var i = 0` with `let i = 0`. In ES6, `let` in a for-loop creates a new binding for each iteration.",
            'verification_rules' => json_encode([
                'must_contain' => ['for (let i = 0;', 'console.log'],
                'must_not_contain' => ['for (var i = 0;']
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'Cannot Read Properties of Undefined',
            'slug' => 'js-cannot-read-property-null',
            'language_slug' => 'javascript',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'runtime',
            'description' => 'A helper function retrieves the user\'s city from a nested profile object. When a user has no address set, the application throws an uncaught TypeError.',
            'broken_code' => "function getUserCity(user) {\n    // Crashes when user.profile or address is missing\n    return user.profile.address.city;\n}\n\nconst guest = { id: 101, profile: null };\nconsole.log(getUserCity(guest));",
            'solution_code' => "function getUserCity(user) {\n    // Uses optional chaining to prevent crash\n    return user?.profile?.address?.city || 'Unknown';\n}\n\nconst guest = { id: 101, profile: null };\nconsole.log(getUserCity(guest));",
            'error_output' => "TypeError: Cannot read properties of null (reading 'address')\n    at getUserCity (index.js:3:25)",
            'expected_behavior' => "Safely return the city if available, or a fallback string without throwing a runtime error.",
            'hint1' => "Accessing nested properties on `null` or `undefined` throws a fatal TypeError in JavaScript.",
            'hint2' => "Modern JavaScript provides the Optional Chaining operator `?.` to safely access deep object properties.",
            'hint3' => "Use `user?.profile?.address?.city` or an `if` check to guard against null.",
            'explanation_bug' => "Accessing property `.address` on `user.profile` when `profile` is `null` causes JavaScript to throw a TypeError.",
            'explanation_why' => "JavaScript cannot dereference properties on primitive null or undefined values, crashing the execution stack.",
            'explanation_fix' => "Use optional chaining (`?.`) like `user?.profile?.address?.city` to safely return undefined/fallback if any link in the chain is nullish.",
            'verification_rules' => json_encode([
                'must_contain' => ['user?.profile?.address?.city'],
                'must_not_contain' => ['user.profile.address.city']
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'String Concatenation Arithmetic Mistake',
            'slug' => 'js-type-coercion-addition',
            'language_slug' => 'javascript',
            'difficulty' => 'medium',
            'level_tier' => 2,
            'bug_type' => 'type_error',
            'description' => 'The shopping cart calculates total order price by adding product price and shipping cost. Instead of $120, it displays $10020!',
            'broken_code' => "function calculateTotal(priceInput, shippingCost) {\n    // priceInput comes from an HTML input field as a string\n    const total = priceInput + shippingCost;\n    return \"$\" + total;\n}\n\n// priceInput: \"100\", shipping: 20\nconsole.log(calculateTotal(\"100\", 20)); // Output: $10020",
            'solution_code' => "function calculateTotal(priceInput, shippingCost) {\n    // Parse priceInput as a number\n    const total = Number(priceInput) + shippingCost;\n    return \"$\" + total;\n}\n\n// priceInput: \"100\", shipping: 20\nconsole.log(calculateTotal(\"100\", 20)); // Output: $120",
            'error_output' => "Output: \"$10020\"\nExpected: \"$120\"",
            'expected_behavior' => "Perform numerical addition rather than string concatenation, producing $120.",
            'hint1' => "The `+` operator in JavaScript performs string concatenation if either operand is a string.",
            'hint2' => "`priceInput` is a string `\"100\"`, so `\"100\" + 20` results in `\"10020\"`.",
            'hint3' => "Convert `priceInput` to a number using `Number(priceInput)` or `parseFloat(priceInput)` before adding.",
            'explanation_bug' => "The `+` operator overloaded behavior coerced the number `20` to string `\"20\"` and concatenated them into `\"10020\"`.",
            'explanation_why' => "In JavaScript, if any operand of the binary `+` operator is a string, JavaScript converts the other operand to a string as well.",
            'explanation_fix' => "Explicitly convert the string operand to a number using `Number(priceInput)` or `parseFloat()` before the addition.",
            'verification_rules' => json_encode([
                'must_contain' => ['Number(priceInput)', 'parseFloat(priceInput)'],
                'must_not_contain' => ['const total = priceInput + shippingCost']
            ]),
            'xp_reward' => 110
        ],

        // ======================== C ========================
        [
            'title' => 'Missing Address-Of Operator in Scanf',
            'slug' => 'c-scanf-missing-ampersand',
            'language_slug' => 'c',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'runtime',
            'description' => 'The program crashes with a Segmentation Fault immediately upon user input in `scanf`.',
            'broken_code' => "#include <stdio.h>\n\nint main() {\n    int age;\n    printf(\"Enter your age: \");\n    // BUG: Missing '&' before age\n    scanf(\"%d\", age);\n    printf(\"You are %d years old.\\n\", age);\n    return 0;\n}",
            'solution_code' => "#include <stdio.h>\n\nint main() {\n    int age;\n    printf(\"Enter your age: \");\n    // Fixed with &age\n    scanf(\"%d\", &age);\n    printf(\"You are %d years old.\\n\", age);\n    return 0;\n}",
            'error_output' => "Segmentation fault (core dumped)\nWarning: format '%d' expects argument of type 'int *', but argument 2 has type 'int'",
            'expected_behavior' => "Store user input correctly into the `age` variable without crashing.",
            'hint1' => "`scanf` requires memory addresses of variables so it knows where to write the input.",
            'hint2' => "Passing `age` by value gives `scanf` whatever random number was in `age` as an address, causing an invalid memory access.",
            'hint3' => "Use the address-of operator `&age` in `scanf(\"%d\", &age)`.",
            'explanation_bug' => "Passing `age` instead of `&age` to `scanf` provides an invalid memory address for writing input.",
            'explanation_why' => "`scanf` requires pointers (`int *`) to store scanned data. Passing an uninitialized `int` by value causes `scanf` to treat that value as an address in memory and write to it, triggering a SIGSEGV.",
            'explanation_fix' => "Pass the address of the variable using `&age`.",
            'verification_rules' => json_encode([
                'must_contain' => ['scanf("%d", &age);'],
                'must_not_contain' => ['scanf("%d", age);']
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'Dangling Pointer & Local Stack Memory',
            'slug' => 'c-dangling-pointer-stack-return',
            'language_slug' => 'c',
            'difficulty' => 'hard',
            'level_tier' => 3,
            'bug_type' => 'memory',
            'description' => 'A helper function attempts to initialize and return an array of integers, but reading the array in `main` produces garbage data or segmentation fault.',
            'broken_code' => "#include <stdio.h>\n\nint* get_scores() {\n    int scores[3] = {95, 88, 72};\n    // BUG: Returning address of local stack variable\n    return scores;\n}\n\nint main() {\n    int* my_scores = get_scores();\n    printf(\"First score: %d\\n\", my_scores[0]);\n    return 0;\n}",
            'solution_code' => "#include <stdio.h>\n#include <stdlib.h>\n\nint* get_scores() {\n    int* scores = (int*)malloc(3 * sizeof(int));\n    scores[0] = 95;\n    scores[1] = 88;\n    scores[2] = 72;\n    return scores;\n}\n\nint main() {\n    int* my_scores = get_scores();\n    printf(\"First score: %d\\n\", my_scores[0]);\n    free(my_scores);\n    return 0;\n}",
            'error_output' => "Warning: function returns address of local variable [-Wreturn-local-addr]\nOutput: First score: 0 (or random garbage)",
            'expected_behavior' => "Dynamically allocate heap memory so the array remains valid after function exit.",
            'hint1' => "Local arrays declared on the stack are deallocated the moment the function returns.",
            'hint2' => "The pointer returned becomes a dangling pointer pointing to reclaimed stack memory.",
            'hint3' => "Allocate the memory on the heap using `malloc(3 * sizeof(int))` and free it in `main`.",
            'explanation_bug' => "Returning the address of a local automatic stack array causes undefined behavior because its stack frame is discarded upon return.",
            'explanation_why' => "Stack memory is scoped strictly to the function call. Once `get_scores()` finishes, `scores` is invalid.",
            'explanation_fix' => "Allocate memory on the heap with `malloc(3 * sizeof(int))` and ensure it is freed with `free()` when done.",
            'verification_rules' => json_encode([
                'must_contain' => ['malloc(', 'free('],
                'must_not_contain' => ['int scores[3] = {95, 88, 72};']
            ]),
            'xp_reward' => 150
        ],

        // ======================== C++ ========================
        [
            'title' => 'Missing Virtual Destructor in Base Class',
            'slug' => 'cpp-missing-virtual-destructor',
            'language_slug' => 'cpp',
            'difficulty' => 'medium',
            'level_tier' => 2,
            'bug_type' => 'memory',
            'description' => 'When deleting a derived object via a base class pointer, the derived destructor is never invoked, causing a silent memory leak.',
            'broken_code' => "#include <iostream>\n\nclass Base {\npublic:\n    Base() { std::cout << \"Base created\\n\"; }\n    // BUG: Destructor is not virtual!\n    ~Base() { std::cout << \"Base destroyed\\n\"; }\n};\n\nclass Derived : public Base {\n    int* buffer;\npublic:\n    Derived() { buffer = new int[100]; std::cout << \"Derived allocated\\n\"; }\n    ~Derived() { delete[] buffer; std::cout << \"Derived cleaned up\\n\"; }\n};\n\nint main() {\n    Base* ptr = new Derived();\n    delete ptr; // Never calls Derived destructor!\n    return 0;\n}",
            'solution_code' => "#include <iostream>\n\nclass Base {\npublic:\n    Base() { std::cout << \"Base created\\n\"; }\n    // Virtual destructor ensures polymorphic cleanup\n    virtual ~Base() { std::cout << \"Base destroyed\\n\"; }\n};\n\nclass Derived : public Base {\n    int* buffer;\npublic:\n    Derived() { buffer = new int[100]; std::cout << \"Derived allocated\\n\"; }\n    ~Derived() { delete[] buffer; std::cout << \"Derived cleaned up\\n\"; }\n};\n\nint main() {\n    Base* ptr = new Derived();\n    delete ptr;\n    return 0;\n}",
            'error_output' => "Output:\nBase created\nDerived allocated\nBase destroyed\n(Note: 'Derived cleaned up' is NEVER printed, buffer is leaked!)",
            'expected_behavior' => "Both Derived and Base destructors should execute upon deletion, properly releasing `buffer`.",
            'hint1' => "Polymorphism in C++ requires virtual dispatch for destructors when deleting through a base class pointer.",
            'hint2' => "Without `virtual`, the compiler statically binds `delete ptr` to `~Base()` only.",
            'hint3' => "Prefix `~Base()` with the `virtual` keyword.",
            'explanation_bug' => "Deleting a derived object through a base pointer with a non-virtual destructor results in undefined behavior and skips the derived class destructor.",
            'explanation_why' => "Without `virtual`, C++ performs static binding at compile time based on the pointer type (`Base*`), bypassing dynamic dispatch in the vtable.",
            'explanation_fix' => "Declare `virtual ~Base()` in the base class to guarantee full polymorphic destruction.",
            'verification_rules' => json_encode([
                'must_contain' => ['virtual ~Base()'],
                'must_not_contain' => ['    ~Base() {']
            ]),
            'xp_reward' => 120
        ],
        [
            'title' => 'Vector Iterator Invalidation During Erase',
            'slug' => 'cpp-iterator-invalidation-erase',
            'language_slug' => 'cpp',
            'difficulty' => 'hard',
            'level_tier' => 3,
            'bug_type' => 'runtime',
            'description' => 'The loop is intended to remove all even numbers from a `std::vector`, but it crashes or skips elements due to iterator invalidation.',
            'broken_code' => "#include <iostream>\n#include <vector>\n\nint main() {\n    std::vector<int> nums = {1, 2, 4, 5, 6};\n    for (auto it = nums.begin(); it != nums.end(); ++it) {\n        if (*it % 2 == 0) {\n            // BUG: erase invalidates 'it', and ++it causes undefined behavior!\n            nums.erase(it);\n        }\n    }\n    return 0;\n}",
            'solution_code' => "#include <iostream>\n#include <vector>\n\nint main() {\n    std::vector<int> nums = {1, 2, 4, 5, 6};\n    for (auto it = nums.begin(); it != nums.end(); ) {\n        if (*it % 2 == 0) {\n            it = nums.erase(it);\n        } else {\n            ++it;\n        }\n    }\n    return 0;\n}",
            'error_output' => "Runtime Error: vector iterator not incrementable / Segmentation Fault",
            'expected_behavior' => "Erase even numbers safely without crashing or skipping consecutive even values.",
            'hint1' => "`nums.erase(it)` invalidates the iterator `it` and all subsequent iterators.",
            'hint2' => "`erase()` returns a new valid iterator pointing to the element immediately following the removed one.",
            'hint3' => "Assign `it = nums.erase(it)` and only advance `++it` when no element was removed.",
            'explanation_bug' => "`std::vector::erase` invalidates iterators at and after the erased element, making subsequent `++it` undefined behavior.",
            'explanation_why' => "When an element is erased from a vector, remaining elements are shifted down. The original iterator is invalidated.",
            'explanation_fix' => "Capture the return value of `erase()` with `it = nums.erase(it)` and only perform `++it` in the `else` branch.",
            'verification_rules' => json_encode([
                'must_contain' => ['it = nums.erase(it);', '} else {', '++it;'],
                'must_not_contain' => ['for (auto it = nums.begin(); it != nums.end(); ++it)']
            ]),
            'xp_reward' => 150
        ],

        // ======================== JAVA ========================
        [
            'title' => 'String Equality with == vs .equals()',
            'slug' => 'java-string-equality-double-equals',
            'language_slug' => 'java',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'logical',
            'description' => 'The authentication check fails even when the user enters the exact correct password "secret123".',
            'broken_code' => "public class AuthCheck {\n    public static boolean checkPassword(String inputPassword) {\n        String actualPassword = new String(\"secret123\");\n        // BUG: Comparing object references with ==\n        if (inputPassword == actualPassword) {\n            return true;\n        }\n        return false;\n    }\n    \n    public static void main(String[] args) {\n        System.out.println(checkPassword(new String(\"secret123\"))); // prints false!\n    }\n}",
            'solution_code' => "public class AuthCheck {\n    public static boolean checkPassword(String inputPassword) {\n        String actualPassword = new String(\"secret123\");\n        // Use .equals() for content comparison\n        if (actualPassword.equals(inputPassword)) {\n            return true;\n        }\n        return false;\n    }\n    \n    public static void main(String[] args) {\n        System.out.println(checkPassword(new String(\"secret123\")));\n    }\n}",
            'error_output' => "Output: false\nExpected: true",
            'expected_behavior' => "Accurately verify that the string contents match, returning true.",
            'hint1' => "In Java, `==` tests object reference equality (memory location), not value equality.",
            'hint2' => "Two distinct String objects can contain the exact same characters while being different instances in memory.",
            'hint3' => "Use `.equals()` to compare the character contents of Strings in Java.",
            'explanation_bug' => "Comparing String objects using `==` checks whether both references point to the exact same memory address rather than comparing their text contents.",
            'explanation_why' => "Objects created with `new String(...)` exist in different heap locations. `==` compares pointer addresses, which are distinct.",
            'explanation_fix' => "Use `actualPassword.equals(inputPassword)` to compare string character sequences.",
            'verification_rules' => json_encode([
                'must_contain' => ['.equals('],
                'must_not_contain' => ['inputPassword == actualPassword']
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'NullPointerException in Auto-Unboxing',
            'slug' => 'java-npe-auto-unboxing',
            'language_slug' => 'java',
            'difficulty' => 'medium',
            'level_tier' => 2,
            'bug_type' => 'runtime',
            'description' => 'The inventory calculator crashes with a `NullPointerException` when processing an item whose stock quantity is unset (`null`).',
            'broken_code' => "public class StockCalculator {\n    public static int calculateTotal(Integer currentStock, int shipment) {\n        // BUG: currentStock can be null; unboxing to int throws NPE\n        int total = currentStock + shipment;\n        return total;\n    }\n\n    public static void main(String[] args) {\n        System.out.println(calculateTotal(null, 25));\n    }\n}",
            'solution_code' => "public class StockCalculator {\n    public static int calculateTotal(Integer currentStock, int shipment) {\n        int baseStock = (currentStock != null) ? currentStock : 0;\n        int total = baseStock + shipment;\n        return total;\n    }\n\n    public static void main(String[] args) {\n        System.out.println(calculateTotal(null, 25));\n    }\n}",
            'error_output' => "Exception in thread \"main\" java.lang.NullPointerException: Cannot invoke \"java.lang.Integer.intValue()\" because \"currentStock\" is null\n\tat StockCalculator.calculateTotal(StockCalculator.java:4)",
            'expected_behavior' => "Treat a null `currentStock` as 0 and return 25 without throwing an exception.",
            'hint1' => "When Java performs arithmetic between a wrapper `Integer` and a primitive `int`, it secretly invokes `currentStock.intValue()`.",
            'hint2' => "If `currentStock` is `null`, invoking `intValue()` throws a NullPointerException.",
            'hint3' => "Check if `currentStock == null` and provide a fallback (e.g. 0) before performing the addition.",
            'explanation_bug' => "Implicit unboxing of a null `Integer` wrapper into primitive `int` triggers an automatic `.intValue()` call on null.",
            'explanation_why' => "Java compilers automatically insert `.intValue()` during arithmetic operations on boxed types. Calling any method on null causes a NullPointerException.",
            'explanation_fix' => "Explicitly check for `currentStock != null ? currentStock : 0` before adding to primitive `shipment`.",
            'verification_rules' => json_encode([
                'must_contain' => ['currentStock != null', ': 0'],
                'must_not_contain' => ['int total = currentStock + shipment;']
            ]),
            'xp_reward' => 120
        ],

        // ======================== PHP ========================
        [
            'title' => 'SQL Injection Vulnerability via Direct Concatenation',
            'slug' => 'php-sql-injection-prepared-statements',
            'language_slug' => 'php',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'database',
            'description' => 'The login handler directly concatenates user input into an SQL query string, creating a critical SQL injection flaw and syntax failure on quotes.',
            'broken_code' => "<?php\nfunction authenticateUser(\$pdo, \$email, \$password) {\n    // BUG: Direct concatenation leads to SQL injection!\n    \$sql = \"SELECT id, name FROM users WHERE email = '\$email' AND password = '\$password'\";\n    \$result = \$pdo->query(\$sql);\n    return \$result->fetch();\n}",
            'solution_code' => "<?php\nfunction authenticateUser(\$pdo, \$email, \$password) {\n    // Use prepared statements with parameterized inputs\n    \$stmt = \$pdo->prepare(\"SELECT id, name FROM users WHERE email = :email AND password = :password\");\n    \$stmt->execute([\n        ':email' => \$email,\n        ':password' => \$password\n    ]);\n    return \$stmt->fetch();\n}",
            'error_output' => "Security Audit Alert: CRITICAL SQL INJECTION (CWE-89)\nSyntax Error when email contains single quotes e.g. o'connor@test.com",
            'expected_behavior' => "Use parameterized prepared statements with PDO to safely escape and execute queries.",
            'hint1' => 'Never interpolate raw variables directly into SQL queries.',
            'hint2' => 'PDO provides $pdo->prepare(...) and $stmt->execute(...).',
            'hint3' => 'Replace the raw string with named parameters (:email, :password) and pass an array to execute().',
            'explanation_bug' => 'String interpolation in SQL allows untrusted input to break out of data context into code execution context, enabling attackers to bypass authentication or steal data.',
            'explanation_why' => 'Without parameterization, characters like \' terminate the SQL string literal, allowing arbitrary SQL commands to be injected.',
            'explanation_fix' => 'Use PDO prepared statements with parameter placeholders (:email) so that the database engine parses the query structure separately from the data values.',
            'verification_rules' => json_encode([
                'must_contain' => ['$pdo->prepare', '$stmt->execute', ':email'],
                'must_not_contain' => ['$pdo->query($sql)']
            ]),
            'xp_reward' => 110
        ],
        [
            'title' => 'Strict In_Array Type Comparison Bug',
            'slug' => 'php-in-array-loose-comparison',
            'language_slug' => 'php',
            'difficulty' => 'medium',
            'level_tier' => 2,
            'bug_type' => 'logical',
            'description' => 'A security gate checks if an entered authorization code matches any prohibited keys. Because of loose comparison, unexpected access is granted.',
            'broken_code' => "<?php\nfunction isBannedUser(\$userId, \$bannedList) {\n    // BUG: in_array defaults to loose comparison (==)\n    // When \$userId is 0 or true, string conversions can match incorrectly!\n    if (in_array(\$userId, \$bannedList)) {\n        return true;\n    }\n    return false;\n}\n\n\$banned = [\"admin_lock\", \"banned_flag\"];\n// In PHP 7/8 loose comparison, 0 == \"admin_lock\" evaluates true in older runtimes\necho isBannedUser(0, \$banned) ? \"Banned\" : \"Allowed\";",
            'solution_code' => "<?php\nfunction isBannedUser(\$userId, \$bannedList) {\n    // Enable strict comparison (===) via third argument true\n    if (in_array(\$userId, \$bannedList, true)) {\n        return true;\n    }\n    return false;\n}\n\n\$banned = [\"admin_lock\", \"banned_flag\"];\necho isBannedUser(0, \$banned) ? \"Banned\" : \"Allowed\";",
            'error_output' => 'Unexpected Result: Evaluated as matching due to loose type coercion in array lookup.',
            'expected_behavior' => 'Enforce strict type checking so integers never loosely match arbitrary strings.',
            'hint1' => '`in_array` takes an optional third argument $strict.',
            'hint2' => 'By default, `in_array` uses loose comparison == instead of ===.',
            'hint3' => 'Pass true as the third parameter: in_array($userId, $bannedList, true).',
            'explanation_bug' => "Default `in_array()` uses loose comparison (`==`), which allows type juggling between numbers, booleans, and strings.",
            'explanation_why' => "Without strict checking, PHP coerces data types, causing subtle and critical security bypasses.",
            'explanation_fix' => "Always pass `true` as the third argument to `in_array(\$needle, \$haystack, true)` for strict `===` comparison.",
            'verification_rules' => json_encode([
                'must_contain' => ['in_array($userId, $bannedList, true)'],
                'must_not_contain' => ['in_array($userId, $bannedList)']
            ]),
            'xp_reward' => 120
        ],

        // ======================== SQL ========================
        [
            'title' => 'The Three-Valued Logic NULL Trap',
            'slug' => 'sql-null-comparison-is-null',
            'language_slug' => 'sql',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'database',
            'description' => 'The HR manager wants a list of all employees who have NOT been assigned a bonus yet. The query runs with 0 errors but unexpectedly returns 0 rows!',
            'broken_code' => "-- BUG: Comparing with = NULL always yields UNKNOWN (false)\nSELECT employee_id, first_name, salary\nFROM employees\nWHERE bonus = NULL;",
            'solution_code' => "-- Use IS NULL for three-valued relational logic\nSELECT employee_id, first_name, salary\nFROM employees\nWHERE bonus IS NULL;",
            'error_output' => "Returned: 0 rows\nExpected: 14 employees who have no bonus recorded in the database.",
            'expected_behavior' => "Return all employees whose `bonus` column contains NULL.",
            'hint1' => "In ANSI SQL, `NULL` represents the absence of a value, not a specific value.",
            'hint2' => "Any comparison using `= NULL` evaluates to `UNKNOWN`, which is treated as false in WHERE filters.",
            'hint3' => "Replace `= NULL` with `IS NULL`.",
            'explanation_bug' => "In SQL three-valued logic (TRUE, FALSE, UNKNOWN), `bonus = NULL` evaluates to `UNKNOWN` for every single row, causing all rows to be filtered out.",
            'explanation_why' => "NULL cannot be equal to anything, not even another NULL. Therefore, the equality operator `=` can never return TRUE when comparing against NULL.",
            'explanation_fix' => "Use the `IS NULL` predicate instead of `= NULL`.",
            'verification_rules' => json_encode([
                'must_contain' => ['WHERE bonus IS NULL'],
                'must_not_contain' => ['bonus = NULL']
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'Missing Column in GROUP BY Aggregation',
            'slug' => 'sql-missing-group-by-column',
            'language_slug' => 'sql',
            'difficulty' => 'medium',
            'level_tier' => 2,
            'bug_type' => 'database',
            'description' => 'The analytics dashboard query fails to calculate average revenue per department and year.',
            'broken_code' => "-- BUG: 'fiscal_year' is selected but not included in GROUP BY\nSELECT department, fiscal_year, AVG(revenue) AS avg_rev\nFROM quarterly_reports\nGROUP BY department;",
            'solution_code' => "-- Include all non-aggregated columns in GROUP BY\nSELECT department, fiscal_year, AVG(revenue) AS avg_rev\nFROM quarterly_reports\nGROUP BY department, fiscal_year;",
            'error_output' => "ERROR 1055 (42000): 'quarterly_reports.fiscal_year' isn't in GROUP BY clause and contains nonaggregated column which is not functionally dependent on columns in GROUP BY clause (ONLY_FULL_GROUP_BY)",
            'expected_behavior' => "Group records properly by both department and fiscal_year to satisfy SQL aggregation standards.",
            'hint1' => "Every column in the `SELECT` list that is not part of an aggregate function (like `AVG`, `SUM`, `COUNT`) must appear in the `GROUP BY` clause.",
            'hint2' => "`fiscal_year` is selected directly without an aggregate wrapper.",
            'hint3' => "Update the `GROUP BY` clause to: `GROUP BY department, fiscal_year;`.",
            'explanation_bug' => "Selecting a non-aggregated column (`fiscal_year`) that is not listed in `GROUP BY` violates SQL standards and fails in modern SQL modes (ONLY_FULL_GROUP_BY).",
            'explanation_why' => "Without grouping by `fiscal_year`, SQL has no way to know which fiscal year value to display for each department group.",
            'explanation_fix' => "Add `fiscal_year` into the `GROUP BY` statement: `GROUP BY department, fiscal_year`.",
            'verification_rules' => json_encode([
                'must_contain' => ['GROUP BY department, fiscal_year'],
                'must_not_contain' => ['GROUP BY department;']
            ]),
            'xp_reward' => 110
        ],

        // ======================== HTML / CSS ========================
        [
            'title' => 'CSS Box Sizing Width Overflow',
            'slug' => 'html-css-box-sizing-overflow',
            'language_slug' => 'html-css',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'ui',
            'description' => 'A modal dialog card with `width: 100%` and `padding: 24px` overflows horizontally beyond its parent screen container, causing ugly horizontal scrollbars.',
            'broken_code' => ".modal-container {\n    width: 100%;\n    padding: 24px;\n    border: 2px solid #3b82f6;\n    /* BUG: default content-box causes width to be 100% + 48px + 4px! */\n    background: #1e293b;\n}",
            'solution_code' => ".modal-container {\n    box-sizing: border-box;\n    width: 100%;\n    padding: 24px;\n    border: 2px solid #3b82f6;\n    background: #1e293b;\n}",
            'error_output' => "UI Render Flaw: Horizontal scrollbar appears. Computed element width is 100% + 52px of padding and borders.",
            'expected_behavior' => "Keep the entire card within 100% of parent width including its padding and borders.",
            'hint1' => "By default in CSS, `box-sizing` is set to `content-box`.",
            'hint2' => "With `content-box`, `padding` and `border` are added ON TOP of the specified `width: 100%`.",
            'hint3' => "Add `box-sizing: border-box;` to include padding and borders inside the specified width.",
            'explanation_bug' => "Default CSS box model (`content-box`) adds padding and border outside the specified width, exceeding 100% of the parent width.",
            'explanation_why' => "Total width = content width (100%) + padding-left (24px) + padding-right (24px) + borders (4px).",
            'explanation_fix' => "Set `box-sizing: border-box;` so padding and borders are factored into the element's total declared width.",
            'verification_rules' => json_encode([
                'must_contain' => ['box-sizing: border-box;'],
                'must_not_contain' => []
            ]),
            'xp_reward' => 100
        ],
        [
            'title' => 'Broken Flexbox Centering & Stretched Items',
            'slug' => 'html-css-flexbox-centering',
            'language_slug' => 'html-css',
            'difficulty' => 'easy',
            'level_tier' => 1,
            'bug_type' => 'ui',
            'description' => 'The developer wanted to center a login card both horizontally and vertically inside a full-height hero section, but the card is glued to the top-left.',
            'broken_code' => ".hero-section {\n    display: flex;\n    height: 100vh;\n    /* BUG: missing horizontal and vertical centering properties */\n}",
            'solution_code' => ".hero-section {\n    display: flex;\n    justify-content: center;\n    align-items: center;\n    height: 100vh;\n}",
            'error_output' => "UI Render Flaw: Child item is positioned at top-left instead of the exact center of the screen.",
            'expected_behavior' => "Center child elements horizontally and vertically within the 100vh container.",
            'hint1' => "`display: flex` by default aligns items along the start of the main axis.",
            'hint2' => "Horizontal alignment along the main axis is controlled by `justify-content`.",
            'hint3' => "Add `justify-content: center;` and `align-items: center;`.",
            'explanation_bug' => "The flex container lacked the cross-axis (`align-items`) and main-axis (`justify-content`) alignment declarations.",
            'explanation_why' => "Without alignment rules, Flexbox defaults to `justify-content: flex-start` and `align-items: stretch`.",
            'explanation_fix' => "Add `justify-content: center;` and `align-items: center;` to the flex container.",
            'verification_rules' => json_encode([
                'must_contain' => ['justify-content: center;', 'align-items: center;'],
                'must_not_contain' => []
            ]),
            'xp_reward' => 100
        ]
    ]
];
