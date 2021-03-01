/* global describe it expect cy */
describe('End-to-end tests for the admin dashboard', () => {
    it('Shows the admin dashboard', () => {
        cy.visit('/admin')
    })
})
