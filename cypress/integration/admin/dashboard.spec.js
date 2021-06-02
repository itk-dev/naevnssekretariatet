/* @todo There should be a better way for authenticating users */

/* global describe it cy */
describe('End-to-end tests for the admin dashboard', () => {
  before(() => {
    cy.visit('/?masquerade=test@example.com')
  })

  it('Shows the admin dashboard', () => {
    cy.visit('/admin')
  })
})
