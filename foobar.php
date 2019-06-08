<?php 
for ($ctr = 1; $ctr <= 100; $ctr++) {
    if ($ctr%3 == 0 && $ctr%5 == 0)
        echo "foobar";
    else if ($ctr%3 == 0)
        echo "foo";
    else if ($ctr%5 == 0)
        echo "bar";
    else
        echo $ctr;

    if ($ctr < 100) 
        echo ", ";
}

?>