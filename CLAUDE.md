# CLAUDE.md - Larafocus

## Project

Larafocus is a Laravel package that integrates with the FocusNFe API. Structure: `src/` contains the main logic, `openapi/` the API specs, `config/` configuration, `tests/` tests.

---

## Clean Architecture Principles (Uncle Bob)

### SOLID

#### SRP - Single Responsibility Principle
- Each class must have **one single reason to change**
- Separate concerns: a class should not fetch data, validate, and format at the same time
- Controllers/Services/Repositories must have distinct roles

#### OCP - Open/Closed Principle
- Classes should be **open for extension, closed for modification**
- Use interfaces and abstractions to allow new behaviors without changing existing code
- Prefer composition and dependency injection over direct modification

#### LSP - Liskov Substitution Principle
- Subclasses must be **substitutable** for their base classes without breaking behavior
- Do not throw unexpected exceptions in interface implementations
- Respect contracts: preconditions cannot be more restrictive, postconditions cannot be more permissive

#### ISP - Interface Segregation Principle
- Prefer **specific interfaces** over large, generic ones
- No class should be forced to implement methods it does not use
- Break large interfaces into smaller, focused ones

#### DIP - Dependency Inversion Principle
- High-level modules **must not depend** on low-level modules; both should depend on abstractions
- Use constructor dependency injection
- Depend on interfaces, not concrete implementations

---

### Functions

- **Small**: functions should be short (ideally < 20 lines)
- **Do one thing**: if a function does more than one thing, extract into subfunctions
- **Few parameters**: ideally 0-2, maximum 3; beyond that, consider an object/DTO
- **No side effects**: functions should not cause unexpected state changes
- **Command-Query Separation**: functions either return something (query) or change state (command), never both

---

### Naming

- **Reveal intention**: the name should tell what the variable/function/class does, without needing a comment
- **Avoid disinformation**: do not use names that can be confused with other concepts
- **Make meaningful distinctions**: `$data1` and `$data2` say nothing; use descriptive names
- **Pronounceable and searchable names**: avoid obscure abbreviations
- **Classes = nouns**, Methods = verbs: `Invoice`, `Company` vs `calculate()`, `send()`

---

### Comments

- **Self-explanatory code** is preferable to comments
- Valid comments: intention, clarification, warning of consequences, TODO
- Bad comments: redundant, misleading, commented-out code, journaling
- **Never** leave commented-out code in the repository; use git for history

---

### Error Handling

- **Use exceptions**, not return codes
- Create domain-specific exceptions when needed
- **Do not return null**: use Null Object Pattern, Optional, or throw an exception
- **Do not pass null** as an argument
- Handle errors at the appropriate level; do not silently swallow exceptions

---

### Formatting

- **Consistency** above all
- Related functions should be placed close together vertically
- Variables declared close to their usage
- Follow PSR-12 for PHP

---

### DRY - Don't Repeat Yourself

- Every piece of knowledge must have a **single, unambiguous representation** in the system
- Abstract duplications into functions, classes, or traits
- But: do not abstract prematurely; duplication is better than the wrong abstraction

---

### Law of Demeter

- An object should only talk to its **immediate neighbors**
- Avoid chains like `$this->getA()->getB()->getC()->doSomething()`
- Each unit should have limited knowledge about other units

---

### Boy Scout Rule

- **Leave the code cleaner than you found it**
- Small continuous improvements prevent degradation

---

### YAGNI - You Aren't Gonna Need It

- **Do not implement** features that are not needed right now
- Solve the current problem in the simplest way possible
- Additional complexity only when justified by real requirements

---

### KISS - Keep It Simple, Stupid

- The simplest solution that works is the best
- Avoid over-engineering and unnecessary abstractions
- Simple code is easier to understand, test, and maintain

---

### Composition over Inheritance

- Prefer **composition** (has-a) over inheritance (is-a)
- Inheritance creates tight coupling between classes
- Use traits sparingly and with clear purpose

---

### Clean Architecture - Dependency Rule

- Dependencies always point **inward** (domain at the center)
- Layers: Entities > Use Cases > Interface Adapters > Frameworks
- The domain **never** depends on frameworks, databases, or external details
- Frameworks are implementation details, not the architecture

---

### Boundaries

- Wrap third-party code behind **your own interfaces** — never let external APIs leak into your domain
- Use the **Adapter pattern** to translate between external data formats and your internal models
- When the external API changes, only the adapter needs to change — the rest of your codebase is protected
- Write **boundary tests** (integration/contract tests) that verify your assumptions about the external API
- This package (Larafocus) *is* a boundary: it isolates Laravel applications from FocusNFe API details

---

### Classes

- Keep classes **small** — measured by responsibilities, not lines of code
- High **cohesion**: every method in a class should use most of the class's instance variables
- If a subset of methods only uses a subset of variables, that's a sign the class should be split
- Avoid **God classes** that know too much or do too much
- A class with too many dependencies (constructor parameters) likely has too many responsibilities

---

### Stepdown Rule / Newspaper Metaphor

- Code should read **top-to-bottom** like a newspaper article
- High-level functions first, implementation details below
- Callers appear above callees
- Each function should lead naturally to the next level of abstraction
- A reader should be able to understand the "what" without scrolling to the "how"

---

### Four Rules of Simple Design (Kent Beck)

In order of priority:

1. **Passes all tests** — correctness is non-negotiable
2. **Reveals intention** — code clearly communicates what it does and why
3. **No duplication** — every piece of knowledge exists in one place (DRY)
4. **Fewest elements** — remove anything that does not serve the first three rules

---

### Successive Refinement

- **First make it work, then make it clean**
- Do not try to write perfect code on the first pass
- Write the working solution, then refactor: extract methods, rename variables, simplify logic
- Clean code is not written — it is **refined**

---

### Data/Object Anti-Symmetry

- **Objects** hide data and expose behavior (encapsulation)
- **Data structures** expose data and have no meaningful behavior (DTOs, arrays)
- Do not mix the two: a class that exposes all its fields *and* has business methods violates both paradigms
- Use DTOs for data transfer, objects for behavior

---

### Code Smells

Watch out for these warning signs:

- **Long Method** — function does too much; extract smaller functions
- **Large Class** — class has too many responsibilities; split it
- **Feature Envy** — a method uses another class's data more than its own; move it
- **Data Clumps** — the same group of parameters appears together repeatedly; extract into an object/DTO
- **Divergent Change** — one class is changed for multiple unrelated reasons; split by responsibility
- **Shotgun Surgery** — one change requires edits across many classes; consolidate related logic
- **Primitive Obsession** — using primitives instead of small value objects (e.g., string for email, CNPJ)
- **Switch Statements** — long switch/if-else chains; consider polymorphism or strategy pattern
- **Dead Code** — unused code; delete it, git remembers

---

## Review Checklist

Before finalizing any code, verify:

1. Does each class have a single responsibility?
2. Are functions small and do one thing only?
3. Are names clear and reveal intention?
4. Is there duplicated code that could be extracted?
5. Does error handling use exceptions properly?
6. Do dependencies point in the correct direction?
7. Do tests cover the critical scenarios?
8. Is the code simple enough?

---

## Testing Principles (Uncle Bob / Clean Code)

### F.I.R.S.T.

- **Fast** — Tests must run quickly. Slow tests don't get run.
- **Independent** — Tests must not depend on each other. Any test should run alone or in any order.
- **Repeatable** — Tests must produce the same result in any environment, every time.
- **Self-Validating** — Tests either pass or fail. No manual inspection of logs or output.
- **Timely** — Write tests at the right time (ideally before the code, TDD).

### Test the Public API, Not Implementation Details

- Tests should exercise the public interface of the system under test.
- Never test private methods or internal state directly — if the behavior matters, it's observable through the public API.
- Reflection in tests is a code smell. Exception: verifying safety-critical invariants (e.g., SSL enforcement in production) where the behavior cannot be observed otherwise.
- If something is hard to test through the public API, the design likely needs to change, not the test.

### One Assertion Per Concept

- Each test should verify one logical concept.
- Multiple asserts are fine when they all verify the same behavior from different angles.
- Avoid testing unrelated things in the same test.

### Arrange-Act-Assert (AAA)

- **Arrange** — Set up the preconditions and inputs.
- **Act** — Execute the behavior under test.
- **Assert** — Verify the expected outcome.
- Keep these sections clearly separated.

### Clean Tests Are Readable

- Tests are documentation. A developer should understand the expected behavior by reading the test.
- Use descriptive test names that state the scenario and expected outcome.
- Minimize noise — use helpers/factories for setup, keep the test body focused on what's being verified.

### Don't Test the Framework

- Don't test that Laravel, Pest, or PHP itself works.
- Test *your* code and *your* business rules.

### Tests Should Be as Easy to Change as Production Code

- If changing an internal detail (renaming a private property, restructuring objects) breaks tests without changing behavior, the tests are coupled to implementation — fix them.
- Tests protect behavior, not structure.