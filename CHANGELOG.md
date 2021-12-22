# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.4.0] - 2021-12-21

### Added

- Statistical graphs are now available as graphical answer types.
- Images can now be added to the graph editor.

## [4.3.3] - 2021-11-15

### Fixed

- MathType in Popup fields now expand when the equation is larger than the default size.
- Fixes a bug that prevented changing the language of a Wiris CAS session.
- Fixes a bug that prevented auxiliary CalcMe contents from being saved when the answer field was left empty.
- Fixes a bug that prevented the proper feedback from being shown when more than one answer were expected cases in a cloze question.
- Fixes a bug that caused some variables with subscripts with array or matrix values to select an element of the array/matrix even if the subscript was not a number.
- Fixes a bug that caused some cloze answer fields to not appear in very old questions.
- Fixed a bug that prevented "Fill with correct answer" to work properly with compound answer questions which had the same left hand side for two or more answers.

## [4.3.2] - 2021-09-28

### Added

- Adds compatibility mode with the plugin filtercodes.

### Fixed

- Fixes a critical bug that prevented some questions from opening with messages such as "expected comma".
- Fixes a bug that prevented some quizzes to be attempted with messages like "Call to member function get() on float".

## [4.3.1] - 2021-09-15

### Fixed

- Restores compatibility with PHP versions prior to 7.2.
