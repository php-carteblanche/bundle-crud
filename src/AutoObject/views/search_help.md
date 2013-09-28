
**The search box of objects'lists pages can be used to filter the table entries with
a full set of rules and <em>logical operators</em> to let user build an advanced and complicated search in data.**

The search is executed word by word and by default in every fields of the object in which it seems pertinent to search.

### Character case and special characters

A search is executed in small case (*'EM' will be the same as 'em'*) and special characters such as accented ones are not allowed and will
be considered, when possible, as their common equivalence (*'é' will be considered as 'e'*).

### Searching in a specific field or table

To search a certain value in a specific table's field you just need to write your search as follow:

    field_name:value

>   A full example should be: `id:23` that will search the entry of the table where ID is equal to 23.

The value here can be either a numeric value or a full search string using operators and rules shown below.

On the same model, to search in a specific table in a page that presents several tables, you just need to write your search as follow:

    table_name:value

You can use both rules above to search a specific field of a specific table:

    table_name:field_name:value

**NOTE:** numeric values are considered to be searched in a numeric field. If you write `23`, the system will search this value in all numeric fields of the table. If your goal is to search occurrences of number 23 in text fields, you need to write `"23"` (*between double-quotes*). This way, the system will consider your search as a string.

### Searching with logical operators

To build a search request, you can write your search string using the following rules and logical operators:

-   `"one two"` (*string between double-quotes*) will return fields containing the entire string between double quotes,
-   `one two` (*two or more words separated by space*) will return fields containing "one" AND "two",
-   `one OR two` (*two or more words separated by 'OR'*) will return fields containing "one" OR "two" ; you can write 'OR' or 'or' at your convenience,
-   `on*` (*a string beginning or ending with an asterisk*) will return fields containing any string beginning by "on" : "one", "only" etc,
-   `(one two)` (*any other suite of rules between parenthesis*) will be searched as an isolated rule.

By default (*if you don't use the asterisk's rule above*), each word of your search is searched as "*a suite of characters*" but not as an entire word, which technically means that it will be surrounded between '%' signs.

By default again (*if you don't use the 'OR' operator*), each group of words will be searched with the 'AND' operator.

>   A full example should be: `"one two" thr* (apple OR pie)` that will search fields containing the string "one two" AND a string beginning with "thr" AND, in an isolated rule, the string "apple" OR the string "pie".

**NOTE:** be careful to not imbricates un-compliant rules, such as writing a parenthesis in a string between double-quotes for example. Your search would not be pertinent as the system won't correctly rebuild your search string.

### Escaping rules characters

If you want to include one of the characters used to build a search rule in a search string, you have to escape it preceding it by a backslash `\`.

>   For example if you want to search the string "one (two)" you will write: `one \(two\)`

This rule is true for the following characters:

-   the asterisk "\*", used for string's completion,
-   the parenthesis "(" and ")", used for rules grouping,
-   the double-quotes '"', used for string isolation,
-   the colon ":", used for field name prefix.

### Rules overview

-   Search is processed word by word.
-   Search is case insensitive and no special character is allowed (*they will be transformed in their equivalent if so*).
-   Numeric value will be searched in numeric fields unless you surround it between double-quotes (*it will be considered as a string*).
-   By default, each word is searched with both ways completion (`%myword%`).
-   An asterisk means any kind of completion of the string at this position but no other.
-   Strings between double-quotes are considered as is.
-   Strings between parenthesis are considered as a group of rules.
-   The default operator is "AND".
-   To use the "OR" operator, write your words separated by ` OR `.
-   To search in a specific field or table, write the field or table name first followed by a colon (`field_name: ...`).
-   The following characters must be escaped preceding them with a backslash to use them "as is": `* ( ) " :`

