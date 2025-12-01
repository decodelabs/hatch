# Hatch — Package Specification

> **Cluster:** `tooling`
> **Language:** `php`
> **Milestone:** `m3`
> **Repo:** `https://github.com/decodelabs/hatch`
> **Role:** Code generation tools

This document describes the purpose, contracts, and design of **Hatch** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Hatch in their own applications or libraries.
- Contributors **maintaining or extending** Hatch.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Hatch provides simple tools for generating PHP code, primarily focused on exporting data structures as static PHP arrays. The package enables conversion of runtime data (arrays, enums, and custom representations) into valid PHP code that can be written to files and loaded statically. This is useful for generating configuration files, cached data structures, and other code artifacts that benefit from being pre-generated rather than computed at runtime.

### 1.2 Non-Goals

Hatch does **not**:

- Provide a full templating engine (see other packages for template rendering)
- Generate complete class definitions or complex code structures
- Perform code analysis or transformation
- Provide a code cache system (see `decodelabs/iota` for code caching)
- Handle file system operations beyond basic template saving (see `decodelabs/atlas` for comprehensive file handling)

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `tooling` (see Chorus taxonomy)
- Hatch is a low-level tooling package with minimal dependencies, designed to be used by other packages that need code generation capabilities. It sits at the foundation of the tooling cluster and is used by packages like Iota (code cache repository) and Zest (Vite integration) for generating PHP code artifacts.

### 2.2 Typical Usage Contexts

Typical places Hatch appears:

- Code generation scripts and build tools
- Configuration file generation
- Static data structure export
- Template-based code generation (when Atlas is available)
- Package development tools that generate code artifacts

Hatch is intended to be used whenever you need to convert runtime data into static PHP code that can be written to files and loaded later.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Hatch`
  Static utility class providing the main code generation method `exportStaticArray()`.

- `DecodeLabs\Hatch\FileTemplate`
  Template-based code generator that interpolates slot placeholders in template files. Requires Atlas to be installed.

- `DecodeLabs\Hatch\Representation`
  Interface for objects that can represent themselves as code strings.

- `DecodeLabs\Hatch\Representation\StaticExpression`
  Interface for objects that represent static PHP expressions (used in array exports).

- `DecodeLabs\Hatch\Proxy\StaticExpression`
  Interface for objects that can export themselves to StaticExpression representations.

- `DecodeLabs\Hatch\Buffer`
  Simple wrapper class implementing Representation for raw code strings.

### 3.2 Main Entry Points

The main usage pattern is exporting arrays to PHP code:

```php
use DecodeLabs\Hatch;

$code = Hatch::exportStaticArray([
    'foo' => 'bar',
    'baz' => 123,
    'nested' => ['a', 'b', 'c']
]);

echo $code; // Outputs formatted PHP array code
```

For template-based generation (requires Atlas):

```php
use DecodeLabs\Hatch\FileTemplate;

$template = new FileTemplate('/path/to/template.php');
$template->setSlot('name', 'MyClass');
$file = $template->saveTo('/path/to/output.php');
```

---

## 4. Dependencies

### 4.1 Direct Decode Labs Dependencies

From `composer.json`:

- `decodelabs/exceptional`
  Enhanced exception handling for error conditions.

**Optional integration:**

- `decodelabs/atlas` (optional, dev dependency)
  Required for `FileTemplate` functionality. Detected at runtime if installed, used for file operations in template generation.

### 4.2 External Dependencies

None required for runtime operation.

See `composer.json` for supported PHP versions.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- `exportStaticArray()` always returns valid PHP array syntax
- Numeric arrays (lists) are exported without explicit keys
- Associative arrays preserve key-value pairs
- String values are properly escaped with `addslashes()`
- Nested arrays maintain proper indentation
- UnitEnum instances are exported as fully qualified enum references
- StaticExpression and StaticExpressionProxy instances are converted to their string representation
- FileTemplate slot interpolation uses `{{ name }}` syntax
- FileTemplate removes shebang lines (`#!...`) from output

### 5.2 Input & Output Contracts

**Hatch::exportStaticArray(array, int level = 1):**
- **Input:** Array containing scalar values, arrays, null, UnitEnum, StaticExpression, or StaticExpressionProxy
- **Output:** Valid PHP array code as string with proper formatting and indentation
- **Preconditions:** None
- **Postconditions:** Output is valid PHP syntax that can be evaluated or written to a file

**FileTemplate::saveTo(string|File):**
- **Input:** File path or File object for output destination
- **Output:** File object representing the saved file
- **Preconditions:** Atlas must be installed, template file must exist
- **Postconditions:** File is created/overwritten with interpolated content, shebang lines removed

**FileTemplate::getSlot(string):**
- **Input:** Slot name
- **Output:** Slot value string or null
- **Preconditions:** None
- **Postconditions:** If slot not set, attempts to generate from built-in generators (e.g., 'date'), otherwise returns null

---

## 6. Error Handling

### 6.1 Exception Types

Hatch throws Exceptional exceptions:

- `Exceptional::ComponentUnavailable`: When Atlas is required but not installed (FileTemplate)
- `Exceptional::Runtime`: When template file cannot be found or other runtime errors occur

All exceptions use the Exceptional pattern for enhanced stack traces and context.

### 6.2 Error Strategy

Hatch uses a fail-fast error strategy. Invalid inputs or missing dependencies result in exceptions being thrown immediately. The package does not attempt to recover from errors or provide fallback behavior.

---

## 7. Configuration & Extensibility

### 7.1 Configuration

No runtime configuration is required. Hatch works out of the box with sensible defaults. The `exportStaticArray()` method accepts an optional `level` parameter for controlling indentation depth (defaults to 1).

### 7.2 Extension Points

Hatch supports extension via:

- **Custom Representation implementations:** Implement `DecodeLabs\Hatch\Representation` interface to create objects that can represent themselves as code
- **StaticExpression implementations:** Implement `DecodeLabs\Hatch\Representation\StaticExpression` to create objects that export as static PHP expressions
- **StaticExpressionProxy implementations:** Implement `DecodeLabs\Hatch\Proxy\StaticExpression` to create objects that can export themselves to StaticExpression
- **FileTemplate slot generators:** Extend `FileTemplate` and override `generateSlot()` to add custom slot generation logic

---

## 8. Interactions with Other Packages

Hatch is designed to be used by packages that need code generation:

- **`decodelabs/iota`**
  Uses Hatch for generating cached code artifacts in its code repository system

- **`decodelabs/zest`**
  Uses Hatch for generating Vite integration code

- **`decodelabs/effigy`**
  Uses Hatch for code generation in CLI tools

- **`decodelabs/atlas`** (optional)
  Required for FileTemplate functionality. Detected at runtime if installed.

Design assumptions:

- Hatch is a lightweight utility with minimal dependencies
- Other packages may extend Hatch's functionality through the Representation interfaces
- FileTemplate is optional functionality that requires Atlas

---

## 9. Usage Examples

### 9.1 Exporting Simple Array

```php
use DecodeLabs\Hatch;

$code = Hatch::exportStaticArray([
    'foo' => 'bar',
    'baz' => 'qux'
]);

// Output:
// [
//     'foo' => 'bar',
//     'baz' => 'qux'
// ]
```

### 9.2 Exporting Nested Arrays

```php
use DecodeLabs\Hatch;

$code = Hatch::exportStaticArray([
    'config' => [
        'database' => [
            'host' => 'localhost',
            'port' => 3306
        ],
        'cache' => true
    ]
]);

// Output maintains proper indentation for nested structures
```

### 9.3 Exporting with Enums

```php
use DecodeLabs\Hatch;

enum Status: string {
    case Active = 'active';
    case Inactive = 'inactive';
}

$code = Hatch::exportStaticArray([
    'status' => Status::Active
]);

// Output:
// [
//     'status' => \Status::Active
// ]
```

### 9.4 Template-Based Generation

```php
use DecodeLabs\Hatch\FileTemplate;

// Template file contains: class {{ name }} { }
$template = new FileTemplate('/path/to/template.php');
$template->setSlot('name', 'MyClass');
$file = $template->saveTo('/path/to/MyClass.php');
```

---

## 10. Implementation Notes (For Contributors)

### 10.1 Internal Architecture

At a high level, Hatch:

- Uses recursive array traversal to generate nested array structures
- Detects numeric vs associative arrays using `array_is_list()` to determine key output format
- Handles type-specific formatting (strings escaped, booleans as 'true'/'false', enums as fully qualified references)
- FileTemplate uses regex-based slot interpolation with `{{ name }}` syntax
- FileTemplate removes shebang lines to ensure generated files are valid PHP

Contributors should:

- Maintain valid PHP syntax output in all code generation methods
- Preserve proper escaping for string values
- Keep the package lightweight with minimal dependencies
- Use Exceptional for all error conditions
- Ensure FileTemplate gracefully handles missing Atlas dependency

### 10.2 Performance Considerations

- `exportStaticArray()` uses string concatenation which is efficient for typical use cases
- Recursive array traversal handles deeply nested structures
- FileTemplate reads template file once and performs in-memory interpolation
- No caching is performed; each call generates fresh output

### 10.3 Gotchas & Historical Decisions

- **Array list detection:** Uses `array_is_list()` to distinguish numeric arrays from associative arrays, affecting key output format
- **Enum handling:** Enums are exported as fully qualified class references (e.g., `\MyEnum::Value`) to ensure they work when the generated code is loaded
- **FileTemplate dependency:** FileTemplate requires Atlas but this is not a hard dependency, allowing Hatch to be used without Atlas for array export functionality
- **Shebang removal:** FileTemplate automatically removes shebang lines to ensure generated files are valid PHP (shebangs are typically only needed in executable scripts)

---

## 11. Testing & Quality

### 11.1 Testing Strategy

Tests should cover:

- Array export with various data types (strings, numbers, booleans, null, arrays, enums)
- Nested array structures with proper indentation
- Numeric vs associative array detection
- String escaping and special character handling
- Enum export format
- StaticExpression and StaticExpressionProxy integration
- FileTemplate slot interpolation
- FileTemplate shebang removal
- Error conditions (missing Atlas, missing template files)

### 11.2 Quality Signals

From the Decode Labs package index (at time of writing):

- **Code:** 2
- **Readme:** 2
- **Docs:** 0
- **Tests:** 0

Hatch is an early-stage package with basic functionality. The code quality is functional but minimal, and the README provides basic usage examples. Comprehensive documentation (this spec) and test coverage are planned but not yet implemented. The package serves its purpose as a simple code generation utility but may be expanded in the future.

---

## 12. Roadmap & Future Ideas

Non-binding ideas:

- Comprehensive test suite covering all export scenarios
- Additional code generation methods beyond array export
- Enhanced template system with more sophisticated slot handling
- Code formatting options (indentation style, line endings)
- Support for generating class definitions and method bodies
- Integration with code style formatters
- Performance optimizations for large array exports

---

## 13. References

- **Chorus docs:**
  - Architecture principles
  - Package taxonomy & clusters
  - Backwards compatibility strategy (once published)

- **Related packages:**
  - `decodelabs/iota` (uses Hatch for code cache generation)
  - `decodelabs/zest` (uses Hatch for Vite integration code)
  - `decodelabs/effigy` (uses Hatch in CLI tools)
  - `decodelabs/atlas` (optional dependency for FileTemplate)

- **Repository:**
  - `https://github.com/decodelabs/hatch`

---

> This spec is intended to stay in sync with the **actual behaviour** of the package.
> When you make significant changes to the public surface or semantics, please update this document and, where applicable, add or update ADRs in Chorus.

