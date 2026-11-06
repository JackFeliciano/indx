# Contributing

This project accepts small, self-contained changes. Everything runs on plain PHP 8.2 with no Composer and no external packages, so a patch that adds a dependency will not merge.

## Before you open a pull request

1. Run the suite. It must stay green.

       php tests/run.php

2. Keep the change inside one concern. A fix to the booking rules does not carry a new stylesheet.
3. Add a check for whatever you changed. The suite is a hand-written runner, so a new test is a new case in the matching file under tests/.
4. Leave the licence headers alone.

## Reporting a defect

Open an issue with the command you ran, the output you saw, and what you expected instead. A defect report that carries a reproduction is a defect that can be closed.

## Writing

Page text and documentation come from the project's own writing hand. Describe what the code does, keep the sentences short, and do not add marketing lines.
