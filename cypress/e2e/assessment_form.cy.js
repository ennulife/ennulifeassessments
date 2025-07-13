/// <reference types="cypress" />

describe('Assessment Form Frontend Interaction', () => {

  beforeEach(() => {
    // Visit the page containing the hair assessment before each test.
    // This path should correspond to the page created in the verification protocol.
    cy.visit('/hair-test/');
  });

  it('should display the assessment form correctly', () => {
    cy.get('.ennu-assessment').should('be.visible');
    cy.get('h2').should('contain.text', 'Hair Assessment');
    cy.get('.progress-bar').should('be.visible');
  });

  it('should calculate and display the age when a date of birth is selected', () => {
    // Select a date that results in a known age.
    // e.g., If current year is 2024, selecting 1990 should result in age 34.
    const currentYear = new Date().getFullYear();
    const birthYear = 1990;
    const expectedAge = currentYear - birthYear;

    cy.get('select[name="dob_year"]').select(birthYear.toString());
    cy.get('select[name="dob_month"]').select('1'); // Select by value "1" for January
    cy.get('select[name="dob_day"]').select('1');

    // The age should be displayed in the designated element.
    cy.get('.calculated-age-display').should('contain.text', `Your age is ${expectedAge}`);
  });

  it('should auto-advance to the next question when a radio button is selected', () => {
    // The first question should be visible initially.
    cy.get('.question-slide[data-question-index="0"]').should('be.visible');
    cy.get('.question-slide[data-question-index="1"]').should('not.be.visible');

    // Click a radio button in the first question.
    cy.get('.question-slide[data-question-index="0"] input[type="radio"]').first().click();

    // After a short delay, the second question should become visible.
    cy.get('.question-slide[data-question-index="0"]', { timeout: 1000 }).should('not.be.visible');
    cy.get('.question-slide[data-question-index="1"]').should('be.visible');
  });

});

describe('Assessment Form Submission', () => {
  it('should allow a new user to complete the hair assessment and redirect to the results page', () => {
    // A unique email is needed for each test run to ensure a new user is created.
    const uniqueId = Date.now();
    const userEmail = `test.user.${uniqueId}@example.com`;

    cy.visit('/hair-test/');

    // --- Fill out the entire form ---

    // Question 1: DOB (Month, Day, Year)
    cy.get('select[name="dob_month"]').select('3'); // March
    cy.get('select[name="dob_day"]').select('15');
    cy.get('select[name="dob_year"]').select('1985');
    cy.get('.next-btn').click();

    // Question 2: Gender
    cy.get('input[name="hair_q2"][value="male"]').click();
    // Auto-advances, no click needed

    // Question 3: Hair Concerns
    cy.get('input[name="hair_q3"][value="receding"]').click();

    // Question 4: Duration
    cy.get('input[name="hair_q4"][value="moderate"]').click();

    // Question 5: Speed
    cy.get('input[name="hair_q5"][value="fast"]').click();

    // Question 6: Family History
    cy.get('input[name="hair_q6"][value="father"]').click();

    // Question 7: Stress Level
    cy.get('input[name="hair_q7"][value="high"]').click();

    // Question 8: Diet Quality
    cy.get('input[name="hair_q8"][value="fair"]').click();

    // Question 9: Previous Treatments
    cy.get('input[name="hair_q9"][value="otc"]').click();

    // Question 10: Goals (Checkbox)
    cy.get('input[name="hair_q10[]"][value="regrow"]').check();
    cy.get('input[name="hair_q10[]"][value="thicken"]').check();
    cy.get('.next-btn').click();

    // Question 11: Contact Info
    cy.get('input[name="contact_info[first_name]"]').type('Cypress');
    cy.get('input[name="contact_info[last_name]"]').type('Test');
    cy.get('input[name="contact_info[email]"]').type(userEmail);
    cy.get('input[name="contact_info[phone]"]').type('555-123-4567');

    // --- Submit the form ---
    cy.get('.submit-btn').click();

    // --- Verify the outcome ---
    // The user should be redirected to the results page.
    cy.url().should('include', '/results/');
    cy.get('h1').should('contain.text', 'Thank You!'); // Assuming a thank you page heading
  });
}); 