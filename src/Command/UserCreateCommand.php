<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'user:create';

    private $passwordEncoder;
    private $em;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em
    ) {
        parent::__construct();
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setDescription('Creates a new user.');
        $this->setHelp('This command allows you to create a new user.');
        $this->addArgument('login', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $userRepository = $this->em->getRepository(User::class);

        $login = $input->getArgument('login');
        $user = $userRepository->findOneBy(['login' => $login]);
        if(!$user)
        {
            $password = $this->getPasswordFromInput("Please enter user password: \n", $input, $output);
            $passwordConfirm = $this->getPasswordFromInput("Confirm the password: \n", $input, $output);

            if($password !== $passwordConfirm)
            {
                throw new \Exception('Passwords do not match');
            }

            $user = new User();
            $user->setLogin($login);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

            $this->em->persist($user);
            $this->em->flush();

            $io->success('User created successfully!');
        }
        else
        {
            $io->error("User with login $login already exists.");
        }
    }

    /**
     * @param string          $questionMessage
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    private function getPasswordFromInput(string $questionMessage, InputInterface $input, OutputInterface $output)
    {
        $question = new Question($questionMessage);
        $helper = $this->getHelper('question');
        $question->setValidator(function($value) {
            if(trim($value) == '')
            {
                throw new \Exception('The password cannot be empty');
            }

            return $value;
        });
        $question->setHidden(true);
        $question->setMaxAttempts(20);
        $password = $helper->ask($input, $output, $question);

        return $password;
    }
}
