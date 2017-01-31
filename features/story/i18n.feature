Feature: Internationalization
  In order to provide textual content of site in different language
  As a developer
  I need to control output content on selected language

  Points:
  - Setup default language 
  - Autoselect language for user
  - Select language by user
  - Store selected language between user sessions
  - Get translation by selected language

  Scenario: Setup default language
    Given there list "[en, ru]" as supported languages
    When I setup the "en" as default language
    Then I should have "en" as default language
    When I setup the "ru" as default language
    Then I should have "ru" as default language
    When I setup the "fr" as default language
    Then I should have "Unsupported language: fr" error

  Scenario Outline: Select current language
    Given there list "<langs>" as supported languages
    When I setup the "<default>" as default language
    And I request the "<select>" as current language
    Then I should have "<setup>" as current language

    Examples:
      | langs        | default | select | setup |
      | [en]         | en      | en     | en    |
      | [en]         | en      | ru     | en    |
      | [en, ru]     | en      | en     | en    |
      | [en, ru]     | en      | ru     | ru    |
      | [en, ru]     | en      | fr     | en    |
      | [en, ru]     | ru      | fr     | ru    |
      | [en, ru, fr] | en      | en     | en    |
      | [en, ru, fr] | en      | ru     | ru    |
      | [en, ru, fr] | en      | fr     | fr    |
      | [en, ru, fr] | ru      | de     | ru    |

  Scenario Outline: Autoselect best language for user
    Given there list "<langs>" as supported languages
    And this "<al string>" as accepted languages
    When I setup the "<default>" as default language
    And I request the best language as current language
    Then I should have "<setup>" as current language

    Examples:
      | langs        | al string                           | default | setup |
      | [en]         | ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4 | en      | en    |
      | [en, ru]     | ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4 | en      | ru    |
      | [en, ru]     | ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4 | ru      | ru    |
      | [en, ru]     | ru-RU;q=0.8,en                      | ru      | en    |
      | [en, ru]     | en-US;q=0.6,en;q=0.4                | ru      | en    |
      | [en, ru]     | fr;q=0.8,ru-RU;q=0.6,en;q=0.4       | en      | ru    |
      | [en, ru, fr] | fr;q=0.8,ru-RU;q=0.6,en;q=0.4       | en      | fr    |
      | [en, ru, fr] | fr;q=0.8,ru-RU,en;q=0.4             | en      | ru    |
      | [en, ru, fr] | be;q=0.8,de;q=0.6                   | en      | en    |
      | [en, ru, fr] | ---                                 | fr      | fr    |

  Scenario Outline: Get translation by selected language
    Given a file named "en.php" with:
      """
      <?php
      return [
        'title' => 'Title',
        'body' => 'Body'
      ];
      """
    And a file named "ru.php" with:
      """
      <?php
      return [
        'title' => 'Заголовок',
        'body' => 'Тело'
      ];
      """
    And there list "<langs>" as supported languages
    And I setup the "<default>" as default language
    When I setup messages dir
    And I request the "<select>" as current language
    And I get translate "<label>" sentence
    Then I should have translated "<text>"
    When I setup messages with:
      """
      {
        "en": {
          "title": "Title",
          "body": "Body"
        },
        "ru": {
          "title": "Заголовок",
          "body": "Тело"
        }
      }
      """
    And I request the "<select>" as current language
    And I get translate "<label>" sentence
    Then I should have translated "<text>"

    Examples:
      | langs        | default | select | label  | text      |
      | [en]         | en      | en     | title  | Title     |
      | [en]         | ru      | en     | body   | Body      |
      | [en]         | en      | en     | header | header    |
      | [en, ru]     | en      | en     | title  | Title     |
      | [en, ru]     | en      | ru     | title  | Заголовок |
      | [en, ru]     | en      | ru     | body   | Тело      |
      | [en, ru]     | en      | ru     | header | header    |
      | [en, ru]     | en      | fr     | title  | Title     |
      | [en, ru]     | ru      | fr     | title  | Заголовок |
      | [en, ru, fr] | en      | fr     | title  | title     |