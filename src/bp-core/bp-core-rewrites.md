# Notes

## Ticket

See [4954](https://buddypress.trac.wordpress.org/ticket/4954)

## Activation strategy

A new option to have a preview of BuddyPress rewrites from the BuddyPress options screen of the WordPress Administration.
A new sub screen to edit slugs

## Backcompat Strategy

Let the user undo and come back to BuddyPress without rewrites without pain.
- Create a new private post type `bp_directories`
- edit the post type of core BuddyPress pages to this private post type on activation
- use it to store rewrite slugs (post_excerpt ?)

## todos

- Edit functions to get BuddyPress links so that they take in account plain permalinks.

## Questions

- bp_get_search_slug() right after root conflicts with WordPress. Do we still need this feature ? See [4154](https://buddypress.trac.wordpress.org/ticket/4154).
